<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksCommand
{
    /** @var EventInterface|BulkEventInterface */
    private object $event;

    /**
     * @param EventInterface|BulkEventInterface $event
     */
    public function __construct(object $event)
    {
        $this->event = $event;
    }

    /**
     * @return EventInterface|BulkEventInterface
     */
    public function event(): object
    {
        return $this->event;
    }
}
