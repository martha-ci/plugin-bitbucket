# plugin-bitbucket

> BitBucket support for Martha Continuous Integration Server

The BitBucket plugin provides OAuth authentication for Martha using
BitBucket, as well as a project provider that allows you to configure
your BitBucket projects to use Martha, and post build plugins to update
the commit status on BitBucket.

## Installation

For now, use Composer to install the plugin:

```
composer require martha-ci/plugin-bitbucket
```

The plugin must be enabled in your `system.local.php` file:

```php
return [
// ...
    'Plugins' => [
        'Martha\Plugin\BitBucket' => []
    ]
// ...
];
```

You'll need to create OAuth credentials on BitBucket for the
authentication piece. See *Manage account / OAuth / OAuth
Consumers* on BitBucket.

Once you have the Client ID and Client Secret, add them to the
`system.local.php` file as configuration options for the plugin:

```php
return [
// ...
    'Plugins' => [
        'Martha\Plugin\BitBucket' => [
            'key' => 'XXX',
            'secret' => 'XXX'
        ]
    ]
// ...
];
```

This installation process will be streamlined in the future.

## Usage

Visiting the `/login` page should now present the user the option to
*Login with BitBucket*. When adding a project, *BitBucket* should now
appear in the project source drop down.
