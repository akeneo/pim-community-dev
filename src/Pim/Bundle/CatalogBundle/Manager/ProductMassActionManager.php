<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;

/**
 * Product mass action manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassActionManager
{
    /** @var ProductMassActionRepositoryInterface */
    protected $massActionRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     */
    public function __construct(
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->massActionRepository = $massActionRepository;
        $this->attributeRepository  = $attributeRepository;
    }

    /**
     * Find common attributes
     * Common attributes are:
     *   - not unique (and not identifier)
     *   - without value AND link to family
     *   - with value
     *
     * @param ProductInterface[] $products
     *
     * @return AttributeInterface[]
     */
    public function findCommonAttributes(array $products)
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $attributeIds = $this->massActionRepository->findCommonAttributeIds($productIds);

        return $this
            ->attributeRepository
            ->findWithGroups(
                array_unique($attributeIds),
                ['conditions' => ['unique' => 0]]
            );
    }

    /**
     * Filter attribute by removing attributes coming from variants
     *
     * @param AttributeInterface[] $attributes
     * @param ProductInterface[]   $products
     *
     * @return AttributeInterface[]
     */
    public function filterAttributesComingFromVariant(array $attributes, array $products)
    {
        $variantAttrCodes = $this->getAttributeCodesComingFromVariantGroups($products);

        $filteredAttributes = [];
        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getCode(), $variantAttrCodes)) {
                $filteredAttributes[] = $attribute;
            }
        }

        return $filteredAttributes;
    }

    /**
     * Filter the locale specific attributes
     *
     * @param AttributeInterface[] $attributes
     * @param string               $currentLocaleCode
     *
     * @return bool
     */
    public function filterLocaleSpecificAttributes(array $attributes, $currentLocaleCode)
    {
        foreach ($attributes as $key => $attribute) {
            if ($attribute->isLocaleSpecific()) {
                $availableCodes = $attribute->getLocaleSpecificCodes();
                if (!in_array($currentLocaleCode, $availableCodes)) {
                    unset($attributes[$key]);
                }
            }
        }

        return $attributes;
    }

    /**
     * Get common attributes coming also from variant groups
     *
     * @param ProductInterface[] $products
     *
     * @return array
     */
    public function getCommonAttributeCodesInVariant(array $products)
    {
        $variantAttrCodes = $this->getAttributeCodesComingFromVariantGroups($products);
        $commonAttributes  = $this->findCommonAttributes($products);

        $commonAttributeCodes = [];
        foreach ($commonAttributes as $attribute) {
            $commonAttributeCodes[] = $attribute->getCode();
        }

        return array_intersect($variantAttrCodes, $commonAttributeCodes);
    }

    /**
     * Get attributes coming from variant groups
     *
     * @param ProductInterface[] $products
     *
     * @return array
     */
    protected function getAttributeCodesComingFromVariantGroups(array $products)
    {
        $variantAttrCodes = [];
        foreach ($products as $product) {
            $variantGroup = $product->getVariantGroup();

            if (null !== $variantGroup && null !== $variantGroup->getProductTemplate()) {
                $variantAttrCodes = array_merge(
                    $variantGroup->getProductTemplate()->getAttributeCodes(),
                    $variantAttrCodes
                );
            }
        }

        return array_unique($variantAttrCodes);
    }
}
