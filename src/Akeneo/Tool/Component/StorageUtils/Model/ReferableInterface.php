<?php

namespace Akeneo\Tool\Component\StorageUtils\Model;

/**
 * Interface for entities with an unique code
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: could be removed in future version to be replaced by a new IdentifiableInterface::getIdentifier to be
 *       consistent with repository
 */
interface ReferableInterface
{
    /**
     * Returns the unique code for the entity
     *
     * @return string
     */
    public function getReference();
}
