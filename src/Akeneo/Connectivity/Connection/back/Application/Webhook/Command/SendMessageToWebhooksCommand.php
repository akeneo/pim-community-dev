<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Pim\Enrichment\Bundle\Message\BusinessEvent;

/**
 * @package   Akeneo\Connectivity\Connection\Application\WebHook\Command
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendMessageToWebhooksCommand
{
    /** @var BusinessEvent */
    private $businessEvent;

    public function __construct(BusinessEvent $businessEvent) {
        $this->businessEvent = $businessEvent;
    }

    public function businessEvent(): BusinessEvent
    {
        return $this->businessEvent;
    }
}