<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The goal of this event subscriber is to catch events, and dispatch them when needed.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BatchEventSubscriberInterface extends EventSubscriberInterface
{
    /**
     * Activate the catch of events.
     */
    public function activate(): void;

    /**
     * Deactivate the catch of events.
     */
    public function deactivate(): void;

    /**
     * Dispatch the events and deactivate for safety.
     */
    public function dispatchAllEvents(): void;
}
