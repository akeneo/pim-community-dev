<?php

namespace Pim\Bundle\CatalogBundle\Manager;

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
    /**
     * @var ProductMassActionRepositoryInterface $productRepository
     */
    protected $massActionRepository;

    /**
     * @var AttributeRepositoryInterface $attributeRepository
     */
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
     * TODO (JJ) FQCN or use statement
     * @param ProductInterface[] $products
     *
     * TODO (JJ) FQCN or use statement
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
     * TODO (JJ) FQCN or use statement
     * @param AttributeInterface[] $attributes
     * TODO (JJ) FQCN or use statement
     * @param ProductInterface[]   $products
     *
     * TODO (JJ) FQCN or use statement
     * @return AttributeInterface[]
     */
    public function filterAttributesComingFromVariant(array $attributes, array $products)
    {
        $variantAttributes = $this->getAttributesComingFromVariantGroups($products);

        $filteredAttributes = [];
        foreach ($attributes as $attribute) {
            // TODO (JJ) kind of weird to make an in_array on AttributeInterface[], it works only if toString is
            // implemented, which we can not be sure
            if (!in_array($attribute->getCode(), $variantAttributes)) {
                $filteredAttributes[] = $attribute;
            }
        }

        return $filteredAttributes;
    }

    /**
     * Filter the locale specific attributes
     *
     * TODO (JJ) FQCN or use statement
     * @param AttributeInterface[] $attributes
     * @param string               $currentLocaleCode
     *
     * @return boolean
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
     * TODO (JJ) FQCN or use statement
     * @param ProductInterface[] $products
     *
     * @return array
     */
    public function getCommonAttributesNotInVariant(array $products)
    {
        $variantAttributes = $this->getAttributesComingFromVariantGroups($products);
        $commonAttributes  = $this->findCommonAttributes($products);

        $commonAttributeCodes = [];
        foreach ($commonAttributes as $attribute) {
            $commonAttributeCodes[] = $attribute->getCode();
        }

        return array_diff($variantAttributes, $commonAttributeCodes);
    }

    /**
     * Get attributes coming from variant groups
     * TODO (JJ) FQCN or use statement
     * @param ProductInterface[] $products
     *
     * TODO (JJ) FQCN or use statement
     * @return AttributeInterface[]
     */
    protected function getAttributesComingFromVariantGroups(array $products)
    {
        $variantAttributes = [];
        foreach ($products as $product) {
            $variantGroup = $product->getVariantGroup();

            if (null !== $variantGroup && null !== $variantGroup->getProductTemplate()) {
                $variantAttributes = array_merge(
                    $variantGroup->getProductTemplate()->getAttributes(),
                    $variantAttributes
                );
            }
        }

        return $variantAttributes;
    }
}
