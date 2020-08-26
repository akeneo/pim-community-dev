<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @package   Akeneo\Connectivity\Connection\Application\WebHook\Command
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendMessageToWebhooksCommand
{
    /** @var BusinessEventInterface */
    private $businessEvent;

    public function __construct(BusinessEventInterface $businessEvent)
    {
        $this->businessEvent = $businessEvent;
    }

    public function businessEvent(): BusinessEventInterface
    {
        return $this->businessEvent;
    }
}
