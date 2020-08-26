<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookRequest
{
    /** @var string */
    private $url;

    /** @var string */
    private $secret;

    /** @var string */
    private $payload;

    public function __construct(string $url, string $secret, array $payload)
    {
        $this->url = $url;
        $this->secret = $secret;
        $this->payload = $payload;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function payload(): array
    {
        return $this->payload;
    }
}
