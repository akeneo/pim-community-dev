<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract copier
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeCopier implements AttributeCopierInterface
{
    /** @var array */
    protected $supportedFromTypes = [];

    /** @var array */
    protected $supportedToTypes = [];

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->productBuilder = $productBuilder;
        $this->attrValidatorHelper = $attrValidatorHelper;

        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $supportsFrom = in_array($fromAttribute->getType(), $this->supportedFromTypes);
        $supportsTo = in_array($toAttribute->getType(), $this->supportedToTypes);

        return $supportsFrom && $supportsTo;
    }

    /**
     * Check locale and scope are valid
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @throws InvalidPropertyException
     */
    protected function checkLocaleAndScope(AttributeInterface $attribute, $locale, $scope)
    {
        try {
            $this->attrValidatorHelper->validateLocale($attribute, $locale);
            $this->attrValidatorHelper->validateScope($attribute, $scope);
        } catch (\LogicException $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }
    }

    /**
     * Configure the option resolver
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['from_locale', 'from_scope', 'to_locale', 'to_scope']);
        $resolver->setDefaults(
            [
                'from_locale' => null,
                'from_scope'  => null,
                'to_locale'   => null,
                'to_scope'    => null
            ]
        );
    }
}
