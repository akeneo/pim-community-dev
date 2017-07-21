<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    public function let(
        NormalizerInterface $normalizer,
        FieldProviderInterface $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider,
        FilterProviderInterface $filterProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        LocalizerInterface $numberLocalizer
    ) {
        $this->beConstructedWith(
            $normalizer,
            $fieldProvider,
            $emptyValueProvider,
            $filterProvider,
            $versionManager,
            $versionNormalizer,
            $structureVersionProvider,
            $numberLocalizer
        );
    }

    function it_normalizes_an_attribute(
        $normalizer,
        $fieldProvider,
        $emptyValueProvider,
        $filterProvider,
        $versionManager,
        $versionNormalizer,
        $structureVersionProvider,
        AttributeInterface $price,
        Version $firstVersion,
        Version $lastVersion
    ) {
        $normalizer->normalize($price, 'standard', Argument::any())->willReturn(
            [
                'code' => 'price',
                'type'                   => 'pim_catalog_price_collection',
                'group'                  => 'marketing',
                'unique'                 => false,
                'useable_as_grid_filter' => true,
                'allowed_extensions'     => null,
                'metric_family'          => null,
                'default_metric_unit'    => null,
                'reference_data_name'    => null,
                'available_locales'      => [],
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => null,
                'number_min'             => null,
                'number_max'             => null,
                'decimals_allowed'       => true,
                'negative_allowed'       => null,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => null,
                'minimum_input_length'   => null,
                'sort_order'             => 0,
                'localizable'            => false,
                'scopable'               => false,
                'labels'                 => [],
                'auto_option_sorting'    => null,
            ]
        );

        $price->getDateMin()->willReturn(null);
        $price->getDateMax()->willReturn(null);
        $emptyValueProvider->getEmptyValue($price)->willReturn([]);
        $fieldProvider->getField($price)->willReturn('akeneo-text-field');
        $filterProvider->getFilters($price)->willReturn(['product-export-builder' => 'akeneo-attribute-string-filter']);
        $price->isLocaleSpecific()->willReturn(false);
        $versionManager->getOldestLogEntry($price)->willReturn($firstVersion);
        $versionManager->getNewestLogEntry($price)->willReturn($lastVersion);
        $versionNormalizer->normalize($firstVersion, 'internal_api')->willReturn('normalizedFirstVersion');
        $versionNormalizer->normalize($lastVersion, 'internal_api')->willReturn('normalizedLastVersion');
        $price->getId()->willReturn(12);
        $structureVersionProvider->getStructureVersion()->willReturn(123789);

        $this->normalize($price, 'internal_api', [])->shouldReturn(
            [
                'code'                   => 'price',
                'type'                   => 'pim_catalog_price_collection',
                'group'                  => 'marketing',
                'unique'                 => false,
                'useable_as_grid_filter' => true,
                'allowed_extensions'     => null,
                'metric_family'          => null,
                'default_metric_unit'    => null,
                'reference_data_name'    => null,
                'available_locales'      => [],
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => null,
                'number_min'             => null,
                'number_max'             => null,
                'decimals_allowed'       => true,
                'negative_allowed'       => null,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => null,
                'minimum_input_length'   => null,
                'sort_order'             => 0,
                'localizable'            => false,
                'scopable'               => false,
                'labels'                 => [],
                'auto_option_sorting'    => null,
                'empty_value'            => [],
                'field_type'             => 'akeneo-text-field',
                'filter_types'           => ['product-export-builder' => 'akeneo-attribute-string-filter'],
                'is_locale_specific'     => false,
                'meta'                   => [
                    'id'                => 12,
                    'created'           => 'normalizedFirstVersion',
                    'updated'           => 'normalizedLastVersion',
                    'structure_version' => 123789,
                    'model_type'        => 'attribute',
                ],
            ]
        );
    }

    function it_normalizes_an_attribute_with_localized_data(
        $normalizer,
        $fieldProvider,
        $emptyValueProvider,
        $filterProvider,
        $versionManager,
        $versionNormalizer,
        $structureVersionProvider,
        $numberLocalizer,
        AttributeInterface $price,
        Version $firstVersion,
        Version $lastVersion
    ) {
        $normalizer->normalize($price, 'standard', Argument::any())->willReturn(
            [
                'code' => 'price',
                'type'                   => 'pim_catalog_price_collection',
                'group'                  => 'marketing',
                'unique'                 => false,
                'useable_as_grid_filter' => true,
                'allowed_extensions'     => null,
                'metric_family'          => null,
                'default_metric_unit'    => null,
                'reference_data_name'    => null,
                'available_locales'      => [],
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => null,
                'number_min'             => '20.5',
                'number_max'             => '4000.8',
                'decimals_allowed'       => true,
                'negative_allowed'       => null,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => null,
                'minimum_input_length'   => null,
                'sort_order'             => 0,
                'localizable'            => false,
                'scopable'               => false,
                'labels'                 => [],
                'auto_option_sorting'    => null,
            ]
        );

        $price->getDateMin()->willReturn(null);
        $price->getDateMax()->willReturn(null);
        $emptyValueProvider->getEmptyValue($price)->willReturn([]);
        $fieldProvider->getField($price)->willReturn('akeneo-text-field');
        $filterProvider->getFilters($price)->willReturn(['product-export-builder' => 'akeneo-attribute-string-filter']);
        $price->isLocaleSpecific()->willReturn(false);
        $numberLocalizer->localize('20.5', ['locale' => 'fr_FR'])->willReturn('20,5');
        $numberLocalizer->localize('4000.8', ['locale' => 'fr_FR'])->willReturn('4000,8');
        $versionManager->getOldestLogEntry($price)->willReturn($firstVersion);
        $versionManager->getNewestLogEntry($price)->willReturn($lastVersion);
        $versionNormalizer->normalize($firstVersion, 'internal_api')->willReturn('normalizedFirstVersion');
        $versionNormalizer->normalize($lastVersion, 'internal_api')->willReturn('normalizedLastVersion');
        $price->getId()->willReturn(12);
        $structureVersionProvider->getStructureVersion()->willReturn(123789);

        $this->normalize($price, 'internal_api', ['locale' => 'fr_FR'])->shouldReturn(
            [
                'code'                   => 'price',
                'type'                   => 'pim_catalog_price_collection',
                'group'                  => 'marketing',
                'unique'                 => false,
                'useable_as_grid_filter' => true,
                'allowed_extensions'     => null,
                'metric_family'          => null,
                'default_metric_unit'    => null,
                'reference_data_name'    => null,
                'available_locales'      => [],
                'max_characters'         => null,
                'validation_rule'        => null,
                'validation_regexp'      => null,
                'wysiwyg_enabled'        => null,
                'number_min'             => '20,5',
                'number_max'             => '4000,8',
                'decimals_allowed'       => true,
                'negative_allowed'       => null,
                'date_min'               => null,
                'date_max'               => null,
                'max_file_size'          => null,
                'minimum_input_length'   => null,
                'sort_order'             => 0,
                'localizable'            => false,
                'scopable'               => false,
                'labels'                 => [],
                'auto_option_sorting'    => null,
                'empty_value'            => [],
                'field_type'             => 'akeneo-text-field',
                'filter_types'           => ['product-export-builder' => 'akeneo-attribute-string-filter'],
                'is_locale_specific'     => false,
                'meta'                   => [
                    'id'                => 12,
                    'created'           => 'normalizedFirstVersion',
                    'updated'           => 'normalizedLastVersion',
                    'structure_version' => 123789,
                    'model_type'        => 'attribute',
                ],
            ]
        );
    }

    function it_supports_attributes_and_internal_api(AttributeInterface $price)
    {
        $this->supportsNormalization($price, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($price, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'internal_api')->shouldReturn(false);
    }
}
