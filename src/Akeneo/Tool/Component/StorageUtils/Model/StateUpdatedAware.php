<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Model;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This interface allows to know whether an entity was updated
 */
interface StateUpdatedAware
{
    /**
     * Whether the entity was updated
     */
    public function isDirty(): bool;

    /**
     * Resets the updated state (to false)
     */
    public function cleanup(): void;
}
