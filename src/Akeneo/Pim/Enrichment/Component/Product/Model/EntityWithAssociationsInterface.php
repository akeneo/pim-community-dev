<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

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

    public function hasAssociationForTypeCode(string $associationTypeCode): bool;

    public function addAssociatedProduct(ProductInterface $product, string $associationTypeCode): void;

    public function removeAssociatedProduct(ProductInterface $product, string $associationTypeCode): void;

    public function getAssociatedProducts(string $associationTypeCode): ?Collection;

    public function addAssociatedProductModel(ProductModelInterface $productModel, string $associationTypeCode): void;

    public function removeAssociatedProductModel(ProductModelInterface $productModel, string $associationTypeCode): void;

    public function getAssociatedProductModels(string $associationTypeCode): ?Collection;

    public function addAssociatedGroup(GroupInterface $group, string $associationTypeCode): void;

    public function removeAssociatedGroup(GroupInterface $group, string $associationTypeCode): void;

    public function getAssociatedGroups(string $associationTypeCode): ?Collection;
}
