<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\Collection;

interface AssociationAwareInterface
{
    /**
     * Get types of associations
     *
     * @return Collection
     */
    public function getAssociations();

    /**
     * Set types of associations
     *
     * @param Collection $associations
     *
     * @return AssociationAwareInterface
     */
    public function setAssociations(Collection $associations);

    /**
     * Add a type of an association
     *
     * @param AssociationInterface $association
     *
     * @throws \LogicException
     *
     * @return AssociationAwareInterface
     */
    public function addAssociation(AssociationInterface $association);

    /**
     * Remove a type of an association
     *
     * @param AssociationInterface $association
     *
     * @return AssociationAwareInterface
     */
    public function removeAssociation(AssociationInterface $association);

    /**
     * Get the product association for an Association type
     *
     * @param AssociationTypeInterface $type
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForType(AssociationTypeInterface $type);

    /**
     * Get the product association for an association type code
     *
     * @param string $typeCode
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForTypeCode($typeCode);
}
