<?php

namespace Pim\Component\Catalog\Model;

/**
 * Variant product. An entity that belongs to a family variant and that contains flexible values,
 * completeness, categories, associations and much more...
 *
 * @author    Julien Janvier <j.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProduct extends AbstractProduct implements VariantProductInterface
{
    /** @var ProductModelInterface $productModel */
    protected $productModel;

    /** @var FamilyVariantInterface */
    protected $familyVariant;

    /**
     * {@inheritdoc}
     */
    public function getProductModel(): ?ProductModelInterface
    {
        return $this->productModel;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductModel(ProductModelInterface $productModel): void
    {
        $this->productModel = $productModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyVariant(): ?FamilyVariantInterface
    {
        return $this->familyVariant;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     */
    public function setFamilyVariant(FamilyVariantInterface $familyVariant): void
    {
        $this->familyVariant = $familyVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariationLevel(): int
    {
        return $this->getProductModel()->getVariationLevel() + 1;
    }
}
