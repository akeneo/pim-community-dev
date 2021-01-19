<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckWebhookReachabilityCommand
{
    /** @var string */
    private $webhookUrl;

    /** @var string */
    private $secret;

    public function __construct(string $webhookUrl, string $secret)
    {
        $this->webhookUrl = $webhookUrl;
        $this->secret = $secret;
    }

    public function webhookUrl(): string
    {
        return $this->webhookUrl;
    }

    public function secret(): string
    {
        return $this->secret;
    }
}
