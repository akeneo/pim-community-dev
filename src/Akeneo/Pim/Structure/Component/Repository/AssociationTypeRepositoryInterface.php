<?php

namespace Akeneo\Pim\Structure\Component\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Association repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationTypeRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Build all association entities not yet linked to a product
     *
     * @param EntityWithAssociationsInterface $entity
     *
     * @return AssociationTypeInterface[]
     */
    public function findMissingAssociationTypes(EntityWithAssociationsInterface $entity);

    /**
     * Return the number of association types
     *
     * @return int
     */
    public function countAll(): int;
}
