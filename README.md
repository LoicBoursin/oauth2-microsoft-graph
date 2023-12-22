# Microsoft Graph Provider for OAuth 2.0 Client

This package provides Microsoft Graph OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Requirements

The following versions of PHP are compatible:
- PHP 8.2
- PHP 8.1
- PHP 8.0

Newer versions may be compatible but have not been tested.

## Installation

To install, use composer:

```
composer require LoicBoursin/oauth2-microsoft-graph
```

## Usage

Usage is the same as The League's OAuth client, using `\LoicBoursin\OAuth2\Client\Provider\MicrosoftUser` as the provider.

#### Managing Scopes and State

When creating your Microsoft authorization URL, you can specify the state and scopes your application may authorize.

If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, the following scopes are available (most important ones) :

- openid
- email
- profile
- (Eventually) User.Read

#### Overriding default values

If you need to override the default values, you can do so by extending the provider the overriding any of the properties or methods, for example:

```php
use LoicBoursin\OAuth2\Client\Provider\Microsoft as MicrosoftProvider;

class MyCustomMicrosoftProvider extends MicrosoftProvider
{
    protected string $urlAuthorize = 'https://login.microsoftonline.com/{TenantId}/oauth2/v2.0/authorize';
    protected string $urlAccessToken = 'https://login.microsoftonline.com/{TenantId}/oauth2/v2.0/token';
}
```

## Testing

``` bash
$ make test
```

## Linting files

```bash
$ make lint
```

## Credits

- [Loïc Boursin](https://github.com/LoicBoursin)
- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/stevenmaguire/oauth2-microsoft/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/LoicBoursin/oauth2-microsoft-graph/blob/master/LICENSE) for more information.

