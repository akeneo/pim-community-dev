<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkEventInterface
{
    /**
     * @return array<EventInterface>
     */
    public function getEvents(): array;
}
