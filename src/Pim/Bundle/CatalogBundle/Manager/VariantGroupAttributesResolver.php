<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Variant group attributes resolver
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupAttributesResolver
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository  = $attributeRepository;
    }

    /**
     * Get non eligible attributes to a product template
     *
     * @param GroupInterface $variantGroup
     *
     * @return AttributeInterface[]
     */
    public function getNonEligibleAttributes(GroupInterface $variantGroup)
    {
        $attributes = $variantGroup->getAxisAttributes()->toArray();

        $template = $variantGroup->getProductTemplate();
        if (null !== $template) {
            foreach (array_keys($template->getValuesData()) as $attributeCode) {
                $attributes[] = $this->attributeRepository->findOneByIdentifier($attributeCode);
            }
        }

        $uniqueAttributes = $this->attributeRepository->findBy(['unique' => true]);
        foreach ($uniqueAttributes as $attribute) {
            if (!in_array($attribute, $attributes)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}
