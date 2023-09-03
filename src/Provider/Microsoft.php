<?php

namespace LoicBoursin\OAuth2\Client\Provider;

use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Microsoft extends AbstractProvider
{
    /**
     * Default scopes.
     *
     * @var array<string>
     */
    public array $defaultScopes = ['openid', 'email', 'profile'];

    /**
     * Base url for authorization.
     */
    protected string $urlAuthorize = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

    /**
     * Base url for access token.
     */
    protected string $urlAccessToken = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

    /**
     * Base url for resource owner.
     */
    protected string $urlResourceOwnerDetails = 'https://graph.microsoft.com/v1.0/me';

    /**
     * Get authorization url to begin OAuth flow.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->urlAuthorize;
    }

    /**
     * Get access token url to retrieve token.
     *
     * @param array<string> $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->urlAccessToken;
    }

    /**
     * Get provider url to fetch user details.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $uri = new Uri($this->urlResourceOwnerDetails);

        return (string) $uri;
    }

    /**
     * Get default scopes.
     *
     * @return array<string>
     */
    protected function getDefaultScopes(): array
    {
        return $this->defaultScopes;
    }

    /**
     * Check a provider response for errors.
     *
     * @param array<string, array<string, string>|string> $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error']['message'] ?? $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response->getBody()
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array<string, string> $response
     */
    protected function createResourceOwner(array $response, AccessToken $token): MicrosoftUser
    {
        return new MicrosoftUser($response);
    }

    /**
     * @param null|AccessToken $token
     *
     * @return array<string, string>
     */
    protected function getAuthorizationHeaders($token = null): array
    {
        return [
            'Authorization' => $token instanceof AccessToken ? sprintf('Bearer %s', $token->getToken()) : '',
        ];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }
}
