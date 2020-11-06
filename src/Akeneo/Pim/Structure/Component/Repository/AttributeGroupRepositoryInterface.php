<?php

namespace Akeneo\Pim\Structure\Component\Repository;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Attribute group repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeGroupRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Find all ordered by label with fallback to default mechanism
     */
    public function getIdToLabelOrderedBySortOrder(): array;

    /**
     * Get the default attribute group
     */
    public function findDefaultAttributeGroup(): ?AttributeGroupInterface;

    /**
     * Find the largest attribute group sort order present in the database
     */
    public function getMaxSortOrder(): int;
}
