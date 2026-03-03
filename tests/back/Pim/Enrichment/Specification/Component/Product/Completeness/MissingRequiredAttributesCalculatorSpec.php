<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

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
        $this->shouldImplement(MissingRequiredAttributesCalculatorInterface::class);
    }

    function it_calculates_the_completeness_of_a_product(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks,
        ProductInterface $entityWithFamily
    ) {
        $family = new Family();
        $family->setCode('accessories');
        $entityWithFamily->getFamily()->willReturn($family);
        $entityWithFamily->getUuid()->willReturn(Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c'));
        $entityWithFamily->getIdentifier()->willReturn('my_bag');
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
            '114c9108-444d-408a-ab43-195068166d2c', 'accessories', [
                'name-ecommerce-en_US',
                'name-ecommerce-fr_FR',
                'desc-<all_channels>-<all_locales>',
                'price-tablet-fr_FR',
                'size-ecommerce-en_US',
            ]
        );
        $getCompletenessProductMasks->fromValueCollection('114c9108-444d-408a-ab43-195068166d2c', 'accessories', $values)->willReturn(
            $productCompletenessMask
        );

        $this->fromEntityWithFamily($entityWithFamily)->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection(
                '114c9108-444d-408a-ab43-195068166d2c', [
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                    new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, []),
                ]
            )
        );
    }

    function it_calculates_the_completeness_of_a_product_model(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks,
        ProductModelInterface $entityWithFamily
    ) {
        $family = new Family();
        $family->setCode('accessories');
        $entityWithFamily->getFamily()->willReturn($family);
        $entityWithFamily->getId()->willReturn(42);
        $entityWithFamily->getIdentifier()->willReturn('my_bag');
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
            42, 'accessories', [
                'name-ecommerce-en_US',
                'name-ecommerce-fr_FR',
                'desc-<all_channels>-<all_locales>',
                'price-tablet-fr_FR',
                'size-ecommerce-en_US',
            ]
        );
        $getCompletenessProductMasks->fromValueCollection(42, 'accessories', $values)->willReturn(
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
