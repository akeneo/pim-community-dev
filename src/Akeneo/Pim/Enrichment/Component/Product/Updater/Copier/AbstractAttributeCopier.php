<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
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

    /** @var EntityWithValuesBuilderInterface */
    protected $entityWithValuesBuilder;

    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param AttributeValidatorHelper         $attrValidatorHelper
     */
    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->attrValidatorHelper     = $attrValidatorHelper;

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

        $sameType = $fromAttribute->getType() === $toAttribute->getType();

        return $supportsFrom && $supportsTo && $sameType;
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
