<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Acl\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventDispatcherAware
{
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
}
