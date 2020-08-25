<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook;

class WebhookRequest
{
    /** @var string */
    private $url;

    /** @var string */
    private $secret;

    /** @var string */
    private $payload;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }
}