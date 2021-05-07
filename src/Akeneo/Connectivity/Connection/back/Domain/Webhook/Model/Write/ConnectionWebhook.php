<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ValueObject\Url;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionWebhook
{
    /** @var string */
    private $code;

    /** @var bool */
    private $enabled;

    /** @var ?Url */
    private $url;

    public function __construct(string $code, bool $enabled, ?string $url = null)
    {
        $this->code = $code;
        $this->enabled = $enabled;
        $this->url = $url ? new Url($url) : null;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function url(): ?Url
    {
        return $this->url;
    }
}
