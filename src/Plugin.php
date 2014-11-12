<?php

namespace Martha\Plugin\BitBucket;

use Martha\Core\Domain\Entity\Build;
use Martha\Core\Plugin\AbstractPlugin;

/**
 * Class Plugin
 * @package Martha\Plugin\BitBucket
 */
class Plugin extends AbstractPlugin
{
    /**
     * Configure and register the plugin.
     *
     * @throws \Exception
     */
    public function init()
    {
        $this->getPluginManager()->registerAuthenticationProvider(
            $this,
            '\Martha\Plugin\BitBucket\Authentication\Provider\BitBucketAuthProvider'
        );
    }
}
