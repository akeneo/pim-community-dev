<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksCommand
{
    public function __construct(private BulkEventInterface $event)
    {
    }

    public function event(): BulkEventInterface
    {
        return $this->event;
    }
}
