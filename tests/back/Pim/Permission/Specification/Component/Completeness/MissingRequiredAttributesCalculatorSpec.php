<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Permission\Component\Completeness\MissingRequiredAttributesCalculator;
use Akeneo\Pim\Permission\Component\Query\GetRawValues;
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
        GetRequiredAttributesMasks $getRequiredAttributesMasks,
        WriteValueCollectionFactory $writeValueCollectionFactory,
        GetRawValues $getRawValues
    ) {
        $this->beConstructedWith(
            $getCompletenessProductMasks,
            $getRequiredAttributesMasks,
            $writeValueCollectionFactory,
            $getRawValues
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MissingRequiredAttributesCalculator::class);
        $this->shouldImplement(MissingRequiredAttributesCalculatorInterface::class);
    }

    function it_calculates_the_completeness_of_a_product_model(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks,
        WriteValueCollectionFactory $writeValueCollectionFactory,
        GetRawValues $getRawValues,
        ProductModelInterface $entityWithFamily
    ) {
        $family = new Family();
        $family->setCode('accessories');
        $entityWithFamily->getFamily()->willReturn($family);
        $entityWithFamily->getId()->willReturn(42);
        $entityWithFamily->getIdentifier()->willReturn('my_bag');
        $entityWithFamily->getCode()->willReturn('my_bag');

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

        $getRawValues->forProductModelId(42)->shouldBeCalledOnce()->willReturn(['the raw values']);
        $values = new WriteValueCollection();
        $writeValueCollectionFactory->createFromStorageFormat(['the raw values'])->willReturn($values);
        $productCompletenessMask = new CompletenessProductMask(
            '42', 'my_bag', 'accessories', [
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
                '42', [
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                    new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, []),
                ]
            )
        );
    }

    function it_calculates_the_completeness_of_a_product(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks,
        WriteValueCollectionFactory $writeValueCollectionFactory,
        GetRawValues $getRawValues,
        ProductInterface $product
    ) {
        $family = new Family();
        $family->setCode('accessories');
        $product->getFamily()->willReturn($family);
        $product->getUuid()->willReturn(Uuid::fromString('df31ba3f-508d-424c-8bc4-446c6e2966e5'));
        $product->getIdentifier()->willReturn('my_bag');

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

        $getRawValues->forProductUuid(Uuid::fromString('df31ba3f-508d-424c-8bc4-446c6e2966e5'))->shouldBeCalledOnce()->willReturn(['the raw values']);
        $values = new WriteValueCollection();
        $writeValueCollectionFactory->createFromStorageFormat(['the raw values'])->willReturn($values);

        $productCompletenessMask = new CompletenessProductMask(
            'df31ba3f-508d-424c-8bc4-446c6e2966e5', 'my_bag', 'accessories', [
                'name-ecommerce-en_US',
                'name-ecommerce-fr_FR',
                'desc-<all_channels>-<all_locales>',
                'price-tablet-fr_FR',
                'size-ecommerce-en_US',
            ]
        );
        $getCompletenessProductMasks->fromValueCollection('df31ba3f-508d-424c-8bc4-446c6e2966e5', 'my_bag', 'accessories', $values)->willReturn(
            $productCompletenessMask
        );

        $this->fromEntityWithFamily($product)->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection(
                'df31ba3f-508d-424c-8bc4-446c6e2966e5', [
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                    new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, []),
                ]
            )
        );
    }
}
