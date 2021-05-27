# Microsoft Graph Provider for OAuth 2.0 Client

This package provides Microsoft Graph OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

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

## Testing

``` bash
$ ./vendor/bin/phpunit
```


## Credits

- [Loïc Boursin](https://github.com/LoicBoursin)
- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/stevenmaguire/oauth2-microsoft/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/LoicBoursin/oauth2-microsoft-graph/blob/master/LICENSE) for more information.

