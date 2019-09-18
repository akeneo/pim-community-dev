<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use PhpSpec\ObjectBehavior;

class MissingRequiredAttributesCalculatorSpec extends ObjectBehavior
{
    function let(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $this->beConstructedWith($getCompletenessProductMasks, $getRequiredAttributesMasks);;
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MissingRequiredAttributesCalculator::class);
    }

    function it_calculates_the_completeness_of_an_entity_with_family(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks,
        ProductModelInterface $entityWithFamily
    ) {
        $family = new Family();
        $family->setCode('accessories');
        $entityWithFamily->getFamily()->willReturn($family);
        $entityWithFamily->getId()->willReturn(42);
        $entityWithFamily->getCode()->willReturn('my_bag');
        $values = new WriteValueCollection();
        $entityWithFamily->getValues()->willReturn($values);

        $requiredAttributesMasksPerChannelAndLocale = [
            new RequiredAttributesMaskForChannelAndLocale(
                'ecommerce',
                'en_US',
                ['name-ecommerce-en_US', 'view-ecommerce-en_US']
            ),
            new RequiredAttributesMaskForChannelAndLocale(
                '<all_channels>',
                '<all_locales>',
                ['desc-<all_channels>-<all_locales>']
            ),
        ];
        $requiredAttributesMask = new RequiredAttributesMask(
            'accessories', $requiredAttributesMasksPerChannelAndLocale
        );
        $getRequiredAttributesMasks->fromFamilyCodes(['accessories'])->willReturn(
            ['accessories' => $requiredAttributesMask]
        );

        $productCompletenessMask = new CompletenessProductMask(
            42, 'my_bag', 'accessories', [
                'name-ecommerce-en_US',
                'name-ecommerce-fr_FR',
                'desc-<all_channels>-<all_locales>',
                'price-tablet-fr_FR',
                'size-ecommerce-en_US',
            ]
        );
        $getCompletenessProductMasks->fromValueCollection(42, 'my_bag', 'accessories', $values)->willReturn(
            $productCompletenessMask
        );

        $this->fromEntityWithFamily($entityWithFamily)->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection(
                42, [
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                    new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, []),
                ]
            )
        );
    }
}
