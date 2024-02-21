<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;

/**
 * All entities who can have a family variant must implement this interface,
 * eg. a product model or a variant product
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityWithFamilyVariantInterface extends EntityWithFamilyInterface
{
    public const ROOT_VARIATION_LEVEL = 0;

    /**
     * @return FamilyVariantInterface|null
     */
    public function getFamilyVariant(): ?FamilyVariantInterface;

    /**
     * @param FamilyVariantInterface $familyVariant
     */
    public function setFamilyVariant(FamilyVariantInterface $familyVariant): void;

    /**
     * Get the variation level of this entity, on a zero-based value.
     * For example, if this entity has 2 parents, it's on level 2.
     * If it has 0 parent, it's on level 0.
     *
     * Which means the oldest is at level 0.
     *
     * @return int
     */
    public function getVariationLevel(): int;

    /**
     * @return ProductModelInterface|null
     */
    public function getParent(): ?ProductModelInterface;

    /**
     * @param ProductModelInterface $parent
     */
    public function setParent(ProductModelInterface $parent = null): void;

    /**
     * @return WriteValueCollection
     */
    public function getValuesForVariation(): WriteValueCollection;
}
