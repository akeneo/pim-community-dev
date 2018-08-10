<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Interface to implement for any entity that should be aware of any associations it is holding.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityWithAssociationsInterface
{
    /**
     * Get types of associations
     *
     * @return Collection | AssociationInterface[]
     */
    public function getAssociations();

    /**
     * Get all the hierarchical associations for the entity
     *
     * @return Collection | AssociationInterface[]
     */
    public function getAllAssociations();

    /**
     * Set types of associations
     *
     * @param Collection $associations
     *
     * @return EntityWithAssociationsInterface
     */
    public function setAssociations(Collection $associations): EntityWithAssociationsInterface;

    /**
     * Add a type of an association
     *
     * @param AssociationInterface $association
     *
     * @throws \LogicException
     *
     * @return EntityWithAssociationsInterface
     */
    public function addAssociation(AssociationInterface $association): EntityWithAssociationsInterface;

    /**
     * Remove a type of an association
     *
     * @param AssociationInterface $association
     *
     * @return EntityWithAssociationsInterface
     */
    public function removeAssociation(AssociationInterface $association): EntityWithAssociationsInterface;

    /**
     * Get the product association for an Association type
     *
     * @param AssociationTypeInterface $type
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForType(AssociationTypeInterface $type): ?AssociationInterface;

    /**
     * Get the product association for an association type code
     *
     * @param string $typeCode
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForTypeCode($typeCode): ?AssociationInterface;
}
