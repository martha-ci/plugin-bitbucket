<?php

namespace Martha\Plugin\BitBucket\Authentication\Provider;

use Bitbucket\API\Http\Listener\OAuthListener;
use Bitbucket\API\Users;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Server\Bitbucket;
use Martha\Core\Authentication\AuthenticationResult;
use Martha\Core\Authentication\Provider\AbstractOAuth1Provider;
use Martha\Core\Domain\Entity\User;
use Martha\Core\Plugin\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BitBucketAuthProvider
 * @package Martha\Plugin\GitHub\Authentication\Provider
 */
class BitBucketAuthProvider extends AbstractOAuth1Provider
{
    /**
     * @var string
     */
    protected $name = 'BitBucket';

    /**
     * @var Bitbucket
     */
    protected $server;

    /**
     * @var TemporaryCredentials
     */
    protected $temporaryCredentials;

    /**
     * @param AbstractPlugin $plugin
     * @param array $config
     */
    public function __construct(AbstractPlugin $plugin, array $config)
    {
        parent::__construct($plugin, $config);

        $this->server = new Bitbucket([
            'identifier' => $config['key'],
            'secret' => $config['secret'],
            'callback_uri' => 'http://martha.local/login/oauth-callback/BitBucket', // todo fixme
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareForRedirect()
    {
        $this->temporaryCredentials = $this->server->getTemporaryCredentials();
        $_SESSION['credentials'] = serialize($this->temporaryCredentials); // todo fixme awful
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->server->getAuthorizationUrl($this->temporaryCredentials);
    }

    /**
     *
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param Request $request
     * @return bool|AuthenticationResult
     */
    public function validateResult(Request $request)
    {
        if (!$request->getGet('oauth_token') || !$request->getGet('oauth_verifier')) {
            return false;
        }

        $temporaryCredentials = unserialize($_SESSION['credentials']); // todo fixme
        $tokenCredentials = $this->server->getTokenCredentials(
            $temporaryCredentials,
            $request->getGet('oauth_token'),
            $request->getGet('oauth_verifier')
        );

        $userInfo = $this->server->getUserDetails($tokenCredentials);

        $result = new AuthenticationResult();
        $result->setName($userInfo->name);
        $result->setAlias($userInfo->nickname);
        $result->setService('BitBucket');
        $result->setCredentials([
            'identifier' => $tokenCredentials->getIdentifier(),
            'secret' => $tokenCredentials->getSecret()
        ]);

        $client = new Users();
        $client->getClient()->addListener(
            new OAuthListener([
                'oauth_consumer_key' => $this->config['key'],
                'oauth_consumer_secret' => $this->config['secret'],
                'oauth_token' => $tokenCredentials->getIdentifier(),
                'oauth_token_secret' => $tokenCredentials->getSecret()
            ])
        );

        $emails = $client->emails()->all($userInfo->nickname);

        if ($emails->getContent()) {
            $emails = json_decode($emails->getContent(), true);
        }

        foreach ($emails as $email) {
            $result->addEmail($email['email']);
        }

        return $result;
    }
}
