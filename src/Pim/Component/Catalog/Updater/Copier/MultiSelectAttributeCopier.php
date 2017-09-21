<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Copy a multi select value attribute in other multi select value attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectAttributeCopier extends AbstractAttributeCopier
{
    /** @var AttributeOptionRepositoryInterface */
    protected $attributeOptionRepository;

    /**
     * @param ProductBuilderInterface                 $productBuilder
     * @param AttributeValidatorHelper                $attrValidatorHelper
     * @param array                                   $supportedFromTypes
     * @param array                                   $supportedToTypes
     * @param AttributeOptionRepositoryInterface|null $attributeOptionRepository
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedFromTypes,
        array $supportedToTypes,
        AttributeOptionRepositoryInterface $attributeOptionRepository = null
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);

        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope);
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope);

        $this->copySingleValue(
            $fromProduct,
            $toProduct,
            $fromAttribute,
            $toAttribute,
            $fromLocale,
            $toLocale,
            $fromScope,
            $toScope
        );
    }

    /**
     * Copy single value
     *
     * @param ProductInterface   $fromProduct
     * @param ProductInterface   $toProduct
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     */
    protected function copySingleValue(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $fromProduct->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $toValue = $toProduct->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addOrReplaceProductValue(
                    $toProduct,
                    $toAttribute,
                    $toLocale,
                    $toScope
                );
            }

            $this->removeOptions($toValue);
            $this->copyOptions($fromValue, $toValue, $toAttribute);
        }
    }

    /**
     * Remove options from attribute
     *
     * @param ProductValueInterface $toValue
     */
    protected function removeOptions(ProductValueInterface $toValue)
    {
        foreach ($toValue->getOptions() as $attributeOption) {
            $toValue->removeOption($attributeOption);
        }
    }

    /**
     * Copy attribute options into a multi select attribute
     *
     * @param ProductValueInterface $fromValue
     * @param ProductValueInterface $toValue
     * @param AttributeInterface    $toAttribute
     */
    protected function copyOptions(
        ProductValueInterface $fromValue,
        ProductValueInterface $toValue,
        AttributeInterface $toAttribute
    ) {
        foreach ($fromValue->getOptions() as $fromAttributeOption) {
            $toValue->addOption($this->getMatchingOptionForAttribute($fromAttributeOption, $toAttribute));
        }
    }

    /**
     * Returns the option of the destination attribute corresponding to the one
     * of the original value.
     *
     * @param AttributeOptionInterface $fromAttributeOption ,
     * @param AttributeInterface       $toAttribute
     *
     * @throws \InvalidArgumentException
     * @return AttributeOptionInterface
     */
    protected function getMatchingOptionForAttribute(
        AttributeOptionInterface $fromAttributeOption,
        AttributeInterface $toAttribute
    ) {
        // TODO: This is the previous, buggy behavior. To remove on master, along with the "= null" of the constructor.
        if (null === $this->attributeOptionRepository) {
            return $fromAttributeOption;
        }

        $optionCode = $fromAttributeOption->getCode();
        $toAttributeCode = $toAttribute->getCode();
        $toOption = $this->attributeOptionRepository->findOneByIdentifier(
            sprintf(
                '%s.%s',
                $toAttributeCode,
                $optionCode
            )
        );

        if (null === $toOption) {
            throw new \InvalidArgumentException(
                sprintf(
                    'There is no valid option "%s" for attribute "%s".',
                    $optionCode,
                    $toAttributeCode
                )
            );
        }

        return $toOption;
    }
}
