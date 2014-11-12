<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;

/**
 * Sets a multi select value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectValueSetter extends AbstractValueSetter
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var AttributeOptionRepository */
    protected $attrOptionRepository;

    /**
     * @param ProductBuilder            $builder
     * @param AttributeOptionRepository $attrOptionRepository
     * @param array                     $supportedTypes
     */
    public function __construct(
        ProductBuilder $builder,
        AttributeOptionRepository $attrOptionRepository,
        array $supportedTypes
    ) {
        $this->productBuilder       = $builder;
        $this->attrOptionRepository = $attrOptionRepository;
        $this->types                = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'setter', 'multi select');
        }

        $attributeOptions = [];
        foreach ($data as $attributeOption) {
            if (!is_array($attributeOption)) {
                throw InvalidArgumentException::arrayOfArraysExpected($attribute->getCode(), 'setter', 'multi select');
            }

            if (!array_key_exists('attribute', $attributeOption)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'attribute',
                    'setter',
                    'multi select'
                );
            }

            if (!array_key_exists('code', $attributeOption)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'code',
                    'setter',
                    'multi select'
                );
            }

            $option = $this->attrOptionRepository->findOneBy(
                ['code' => $attributeOption['code'], 'attribute' => $attribute]
            );
            if (null === $option) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'code',
                    sprintf('Option with code "%s" does not exist', $attributeOption['code']),
                    'setter',
                    'multi select'
                );
            }

            $attributeOptions[] = $option;
        }

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }

            foreach ($value->getOptions() as $option) {
                $value->removeOption($option);
            }

            foreach ($attributeOptions as $attributeOption) {
                $value->addOption($attributeOption);
            }
        }
    }
}
