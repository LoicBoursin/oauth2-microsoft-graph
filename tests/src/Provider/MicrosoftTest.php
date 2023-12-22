<?php

namespace LoicBoursin\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Stream;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use LoicBoursin\OAuth2\Client\Provider\Microsoft;
use LoicBoursin\OAuth2\Client\Provider\MicrosoftUser;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class MicrosoftTest extends TestCase
{
    use QueryBuilderTrait;

    protected Microsoft $provider;

    protected function setUp(): void
    {
        $this->provider = new Microsoft([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
        parent::setUp();
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();

        /** @var array<string, string> $uri */
        $uri = parse_url($url);

        $this->assertArrayHasKey('query', $uri);

        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes(): void
    {
        $scopeSeparator = ' ';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);

        $this->assertStringContainsString($encodedScope, $url);
    }

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();

        /** @var array<string, string> $uri */
        $uri = parse_url($url);

        $this->assertArrayHasKey('path', $uri);
        $this->assertEquals('/common/oauth2/v2.0/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);

        /** @var array<string, string> $uri */
        $uri = parse_url($url);

        $this->assertArrayHasKey('path', $uri);
        $this->assertEquals('/common/oauth2/v2.0/token', $uri['path']);
    }

    public function testSettingAuthEndpoints(): void
    {
        $customAuthUrl = uniqid();
        $customTokenUrl = uniqid();
        $customResourceOwnerUrl = uniqid();

        /** @var AccessToken $token */
        $token = $this->createMock(AccessToken::class);

        $this->provider = new Microsoft([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'urlAuthorize' => $customAuthUrl,
            'urlAccessToken' => $customTokenUrl,
            'urlResourceOwnerDetails' => $customResourceOwnerUrl,
        ]);

        $authUrl = $this->provider->getAuthorizationUrl();
        $this->assertStringContainsString($customAuthUrl, $authUrl);
        $tokenUrl = $this->provider->getBaseAccessTokenUrl([]);
        $this->assertStringContainsString($customTokenUrl, $tokenUrl);
        $resourceOwnerUrl = $this->provider->getResourceOwnerDetailsUrl($token);
        $this->assertStringContainsString($customResourceOwnerUrl, $resourceOwnerUrl);
    }

    public function testGetAccessToken(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->createStreamFromResponseArray([
                'access_token' => 'mock_access_token',
                'authentication_token' => '',
                'code' => '',
                'expires_in' => 3600,
                'refresh_token' => 'mock_refresh_token',
                'scope' => '',
                'state' => '',
                'token_type' => '',
            ]))
        ;
        $response
            ->expects($this->once())
            ->method('getHeader')
            ->willReturn(['content-type' => 'json'])
        ;

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('send')
            ->willReturn($response)
        ;
        $this->provider->setHttpClient($client);

        /** @var AccessToken $token */
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData(): void
    {
        $email = uniqid();
        $firstname = uniqid();
        $lastname = uniqid();
        $name = uniqid();
        $userId = rand(1000, 9999);
        $urls = uniqid();

        $postResponse = $this->createMock(ResponseInterface::class);
        $postResponse
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->createStreamFromResponseArray([
                'access_token' => 'mock_access_token',
                'authentication_token' => '',
                'code' => '',
                'expires_in' => 3600,
                'refresh_token' => 'mock_refresh_token',
                'scope' => '',
                'state' => '',
                'token_type' => '',
            ]))
        ;
        $postResponse
            ->expects($this->once())
            ->method('getHeader')
            ->willReturn(['content-type' => 'json'])
        ;

        $userResponse = $this->createMock(ResponseInterface::class);

        $userResponse
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->createStreamFromResponseArray([
                'id' => $userId,
                'displayName' => $name,
                'givenName' => $firstname,
                'surname' => $lastname,
                'userPrincipalName' => $email,
                'link' => $urls,
            ]))
        ;
        $userResponse
            ->expects($this->once())
            ->method('getHeader')
            ->willReturn(['content-type' => 'json'])
        ;

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->exactly(2))
            ->method('send')
            ->willReturn($postResponse, $userResponse)
        ;
        $this->provider->setHttpClient($client);

        /** @var AccessToken $token */
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        /** @var MicrosoftUser $user */
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['userPrincipalName']);
        $this->assertEquals($firstname, $user->getFirstname());
        $this->assertEquals($firstname, $user->toArray()['givenName']);
        $this->assertEquals($lastname, $user->getLastname());
        $this->assertEquals($lastname, $user->toArray()['surname']);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($name, $user->toArray()['displayName']);
        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
        $this->assertEquals($urls.'/cid-'.$userId, $user->getUrls());
        $this->assertEquals($urls.'/cid-'.$userId, $user->toArray()['link'].'/cid-'.$user->toArray()['id']);
    }

    public function testExceptionThrownWhenErrorObjectReceived(): void
    {
        $this->expectException(IdentityProviderException::class);

        $message = uniqid();

        $postResponse = $this->createMock(ResponseInterface::class);
        $postResponse
            ->expects($this->any())
            ->method('getBody')
            ->willReturn($this->createStreamFromResponseArray([
                'error' => [
                    'code' => 'request_token_expired',
                    'message' => $message,
                ],
            ]))
        ;
        $postResponse
            ->expects($this->any())
            ->method('getHeaders')
            ->willReturn(['content-type' => 'json'])
        ;
        $postResponse
            ->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(500)
        ;

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('send')
            ->willReturn($postResponse)
        ;
        $this->provider->setHttpClient($client);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function createStreamFromResponseArray(array $response): StreamInterface
    {
        $jsonEncodedResponse = json_encode($response);

        \is_string($jsonEncodedResponse) ?: throw new \RuntimeException('Unable to encode response to JSON');

        $streamResponse = fopen(sprintf('data://text/plain,%s', $jsonEncodedResponse), 'r');

        false !== $streamResponse ?: throw new \RuntimeException('Unable to create stream from response');

        return new Stream($streamResponse);
    }
}
