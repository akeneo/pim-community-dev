<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverterRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Copies attribute data from different attribute types
 */
final class HeterogeneousAttributeCopier implements AttributeCopierInterface
{
    /** @var ValueDataConverterRegistry */
    private $valueDataConverterRegistry;

    /** @var EntityWithValuesBuilderInterface */
    private $entityWithValuesBuilder;

    /** @var AttributeValidatorHelper */
    private $attrValidatorHelper;

    /** @var OptionsResolver */
    private $resolver;

    public function __construct(
        ValueDataConverterRegistry $valueDataConverterRegistry,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->valueDataConverterRegistry = $valueDataConverterRegistry;
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->resolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute): bool
    {
        return $fromAttribute->getType() !== $toAttribute->getType() &&
            null !== $this->valueDataConverterRegistry->getDataConverter($fromAttribute, $toAttribute);
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ): void {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope);
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope);

        $fromValue = $fromEntityWithValues->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        $targetData = null !== $fromValue ? $this->valueDataConverterRegistry->getDataConverter($fromAttribute, $toAttribute)->convert(
            $fromValue,
            $toAttribute
        ) : null;

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $toEntityWithValues,
            $toAttribute,
            $toLocale,
            $toScope,
            $targetData
        );
    }

    private function checkLocaleAndScope(AttributeInterface $attribute, $locale, $scope): void
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

    private function configureOptions(): void
    {
        $this->resolver->setRequired(['from_locale', 'from_scope', 'to_locale', 'to_scope']);
        $this->resolver->setDefaults(
            [
                'from_locale' => null,
                'from_scope' => null,
                'to_locale' => null,
                'to_scope' => null,
            ]
        );
    }
}
