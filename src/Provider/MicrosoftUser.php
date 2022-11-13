<?php

namespace LoicBoursin\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MicrosoftUser implements ResourceOwnerInterface
{
    /**
     * Raw response.
     *
     * @var array<string, string>
     */
    protected array $response;

    /**
     * Creates new resource owner.
     *
     * @param array<string, string> $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * Get user id.
     */
    public function getId(): ?string
    {
        return $this->response['id'] ?: null;
    }

    /**
     * Get user email.
     */
    public function getEmail(): ?string
    {
        return $this->response['userPrincipalName'] ?: null;
    }

    /**
     * Get user firstname.
     */
    public function getFirstname(): ?string
    {
        return $this->response['givenName'] ?: null;
    }

    /**
     * Get user lastname.
     */
    public function getLastname(): ?string
    {
        return $this->response['surname'] ?: null;
    }

    /**
     * Get username.
     */
    public function getName(): ?string
    {
        return $this->response['displayName'] ?: null;
    }

    /**
     * Get user urls.
     */
    public function getUrls(): ?string
    {
        return isset($this->response['link']) ? $this->response['link'].'/cid-'.$this->getId() : null;
    }

    /**
     * Return all the owner details available as an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
