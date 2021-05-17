<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateWebhookCommand
{
    private string $code;

    private bool $enabled;

    private ?string $url;

    public function __construct(string $code, bool $enabled, ?string $url = null)
    {
        $this->code = $code;
        $this->enabled = $enabled;
        $this->url = $url;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function url(): ?string
    {
        return $this->url;
    }
}
