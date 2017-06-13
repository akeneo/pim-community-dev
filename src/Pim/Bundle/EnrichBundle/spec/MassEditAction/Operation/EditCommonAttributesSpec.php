<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditCommonAttributesSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $internalNormalizer,
        AttributeConverterInterface $localizedConverter,
        LocalizerRegistryInterface $localizerRegistry,
        CollectionFilterInterface $productValuesFilter,
        ConverterInterface $converter
    ) {
        $this->beConstructedWith(
            $productBuilder,
            $userContext,
            $attributeRepository,
            $productUpdater,
            $productValidator,
            $internalNormalizer,
            $localizedConverter,
            $localizerRegistry,
            $productValuesFilter,
            $converter,
            'edit_common_attributes'
        );
    }

    function it_sets_and_gets_values()
    {
        $this->getValues()->shouldReturn('');
        $this->setValues('Values');
        $this->getValues()->shouldReturn('Values');
    }

    function it_gets_the_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_edit_common_attributes');
    }

    function it_gets_the_operation_alias()
    {
        $this->getOperationAlias()->shouldReturn('edit-common-attributes');
    }

    function it_gets_the_batch_job_code()
    {
        $this->getJobInstanceCode()->shouldReturn('edit_common_attributes');
    }

    function it_gets_configuration($userContext, LocaleInterface $locale)
    {
        $locale->getCode()->willReturn('fr_FR');
        $expected = [
            'filters' => [],
            'actions' => [
                'normalized_values' => '',
                'ui_locale'         => 'fr_FR',
                'attribute_locale'  => null,
                'attribute_channel' => null,
            ]
        ];

        $userContext->getUiLocale()->willReturn($locale);
        $this->getBatchConfig()->shouldReturn($expected);
    }

    function it_sanitizes_data_during_finalization(
        $userContext,
        $attributeRepository,
        $localizerRegistry,
        $productValuesFilter,
        LocaleInterface $fr,
        AttributeInterface $normalAttr,
        AttributeInterface $scopableAttr,
        AttributeInterface $localisableAttr,
        AttributeInterface $localiedAttr,
        LocalizerInterface $localizer,
        ConverterInterface $converter
    ) {

        $attributeRepository->findOneByIdentifier('normal_attr')->willReturn($normalAttr);
        $attributeRepository->findOneByIdentifier('scopable_attr')->willReturn($scopableAttr);
        $attributeRepository->findOneByIdentifier('localisable_attr')->willReturn($localisableAttr);
        $attributeRepository->findOneByIdentifier('localised_attr')->willReturn($localiedAttr);

        $normalAttr->getType()->willReturn('not_localized');
        $scopableAttr->getType()->willReturn('not_localized');
        $localisableAttr->getType()->willReturn('not_localized');
        $localiedAttr->getType()->willReturn('localized');

        $localizerRegistry->getLocalizer('not_localized')->willReturn(null);
        $localizerRegistry->getLocalizer('localized')->willReturn($localizer);

        $fr->getCode()->willReturn('fr');
        $userContext->getUiLocale()->willReturn($fr);

        $this->setAttributeLocale('fr');
        $this->setAttributeChannel('tablet');

        $rawData = [
            'normal_attr' => [['data' => 'foo', 'scope' => null, 'locale' => null]],
            'scopable_attr' => [
                ['data' => 'foo', 'scope' => 'tablet', 'locale' => null],
                ['data' => 'bar', 'scope' => 'ecommerce', 'locale' => null]
            ],
            'localisable_attr' => [
                ['data' => 'foo', 'scope' => null, 'locale' => 'fr'],
                ['data' => 'bar', 'scope' => null, 'locale' => 'de']
            ],
            'localised_attr' => [
                ['data' => [
                    ['amount' => '45,59', 'currency' => 'EUR'],
                    ['amount' => '18,22', 'currency' => 'USD'],
                ], 'scope' => null, 'locale' => null]
            ],
        ];

        $converter->convert($rawData)->willReturn($rawData);

        $this->setValues(json_encode($rawData));

        $localizer->delocalize(
            [['amount' => '45,59', 'currency' => 'EUR'], ['amount' => '18,22', 'currency' => 'USD']],
            ["locale" => "fr"]
        )->willReturn([['amount' => '45.59', 'currency' => 'EUR'], ['amount' => '18.22', 'currency' => 'USD']]);

        $sanitizedData = [
            'normal_attr' => [['data' => 'foo', 'scope' => null, 'locale' => null]],
            'scopable_attr' => [
                ['data' => 'foo', 'scope' => 'tablet', 'locale' => null],
            ],
            'localisable_attr' => [
                ['data' => 'foo', 'scope' => null, 'locale' => 'fr'],
            ],
            'localised_attr' => [
                ['data' => [
                    ['amount' => '45.59', 'currency' => 'EUR'],
                    ['amount' => '18.22', 'currency' => 'USD'],
                ], 'scope' => null, 'locale' => null]
            ],
        ];

        $localizedData = [
            'normal_attr' => [['data' => 'foo', 'scope' => null, 'locale' => null]],
            'scopable_attr' => [
                ['data' => 'foo', 'scope' => 'tablet', 'locale' => null],
            ],
            'localisable_attr' => [
                ['data' => 'foo', 'scope' => null, 'locale' => 'fr'],
            ],
            'localised_attr' => [
                ['data' => [
                    ['amount' => '45,59', 'currency' => 'EUR'],
                    ['amount' => '18,22', 'currency' => 'USD'],
                ], 'scope' => null, 'locale' => null]
            ],
        ];

        $productValuesFilter->filterCollection($localizedData, 'pim.internal_api.product_values_data.edit')
            ->willReturn($localizedData);

        $this->finalize();
        $this->getValues()->shouldReturn(json_encode($sanitizedData));
    }
}
