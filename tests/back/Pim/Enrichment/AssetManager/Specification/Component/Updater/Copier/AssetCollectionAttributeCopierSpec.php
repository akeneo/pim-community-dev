<?php


namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Updater\Copier;


use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetCollectionAttributeCopierSpec extends ObjectBehavior
{
    function let(EntityWithValuesBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['pim_catalog_asset_collection'],
            ['pim_catalog_asset_collection']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement(CopierInterface::class);
    }

    function it_supports_asset_collection_attributes_bound_to_the_same_asset_family(
        AttributeInterface $textareaAttribute,
        AttributeInterface $packshotAttribute,
        AttributeInterface $noticeAttribute
    ) {
        $packshotAttribute->getType()->willReturn('pim_catalog_asset_collection');
        $packshotAttribute->getReferenceDataName()->willReturn('packshot_promo');

        $noticeAttribute->getType()->willReturn('pim_catalog_asset_collection');
        $noticeAttribute->getReferenceDataName()->willReturn('tv_notice');

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $textareaAttribute->getReferenceDataName()->willReturn(null);

        $this->supportsAttributes($packshotAttribute, $packshotAttribute)->shouldReturn(true);
        $this->supportsAttributes($packshotAttribute, $noticeAttribute)->shouldReturn(false);
        $this->supportsAttributes($textareaAttribute, $noticeAttribute)->shouldReturn(false);
        $this->supportsAttributes($packshotAttribute, $textareaAttribute)->shouldReturn(false);
    }

    function it_copies_an_asset_collection_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');
        $fromAttribute->getReferenceDataName()->willReturn('packshot_promo');
        $toAttribute->getReferenceDataName()->willReturn('packshot_promo');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromValue = AssetCollectionValue::scopableLocalizableValue(
            'packshot_promo',
            [
                AssetCode::fromString('asset_code_1'),
                AssetCode::fromString('asset_code_2'),
            ],
            'mobile',
            'fr_FR'
        );
        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromValue);
        $builder
            ->addOrReplaceValue($product1, $toAttribute, $toLocale, $toScope, ['asset_code_1', 'asset_code_2'])
            ->shouldBeCalled();

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder->addOrReplaceValue($product2, $toAttribute, $toLocale, $toScope, null)->shouldBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }
}
