<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkEventInterface extends TenantAwareInterface
{
    /**
     * @return array<EventInterface>
     */
    public function getEvents(): array;
}
