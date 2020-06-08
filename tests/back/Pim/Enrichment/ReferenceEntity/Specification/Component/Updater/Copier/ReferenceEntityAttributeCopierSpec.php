<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Copier\ReferenceEntityAttributeCopier;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

class ReferenceEntityAttributeCopierSpec extends ObjectBehavior
{
    function let(EntityWithValuesBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['akeneo_reference_entity'],
            ['akeneo_reference_entity']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement(CopierInterface::class);
        $this->shouldHaveType(ReferenceEntityAttributeCopier::class);
    }

    function it_supports_reference_entity_values(
        AttributeInterface $textareaAttribute,
        AttributeInterface $designerAttribute,
        AttributeInterface $cityAttribute
    ) {
        $designerAttribute->getType()->willReturn('akeneo_reference_entity');
        $designerAttribute->getReferenceDataName()->willReturn('designers');

        $cityAttribute->getType()->willReturn('pim_catalog_asset_collection');
        $cityAttribute->getReferenceDataName()->willReturn('cities');

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $textareaAttribute->getReferenceDataName()->willReturn(null);

        $this->supportsAttributes($designerAttribute, $designerAttribute)->shouldReturn(true);
        $this->supportsAttributes($cityAttribute, $designerAttribute)->shouldReturn(false);
        $this->supportsAttributes($textareaAttribute, $cityAttribute)->shouldReturn(false);
        $this->supportsAttributes($designerAttribute, $textareaAttribute)->shouldReturn(false);
    }

    function it_copies_a_reference_entity_single_link_value(
        EntityWithValuesBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        ProductInterface $product,
        AttributeInterface $refDesigner
    ) {
        $options = [
            'from_locale' => 'en_US',
            'to_locale' => 'fr_FR',
            'from_scope' => 'mobile',
            'to_scope' => 'ecommerce',
        ];

        $refDesigner->getCode()->willReturn('ref_designer');
        $refDesigner->getReferenceDataName()->willReturn('designers');

        $attrValidatorHelper->validateLocale($refDesigner, 'en_US')->shouldBeCalled();
        $attrValidatorHelper->validateLocale($refDesigner, 'fr_FR')->shouldBeCalled();
        $attrValidatorHelper->validateScope($refDesigner, 'mobile')->shouldBeCalled();
        $attrValidatorHelper->validateScope($refDesigner, 'ecommerce')->shouldBeCalled();

        $product->getValue('ref_designer', 'en_US', 'mobile')->willReturn(
            ReferenceEntityValue::scopableLocalizableValue(
                'ref_designer',
                RecordCode::fromString('starck'),
                'mobile',
                'en_US'
            )
        );
        $builder->addOrReplaceValue($product, $refDesigner, 'fr_FR', 'ecommerce', 'starck')->shouldBeCalled();

        $this->copyAttributeData(
            $product,
            $product,
            $refDesigner,
            $refDesigner,
            $options
        );
    }

    function it_copies_a_reference_entity_collection_value(
        EntityWithValuesBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        ProductInterface $product,
        AttributeInterface $refDesigners
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['akeneo_reference_entity_collection'],
            ['akeneo_reference_entity_collection']
        );

        $options = [
            'from_locale' => 'en_US',
            'to_locale' => 'fr_FR',
            'from_scope' => 'mobile',
            'to_scope' => 'ecommerce',
        ];

        $refDesigners->getCode()->willReturn('ref_designer');
        $refDesigners->getReferenceDataName()->willReturn('designers');

        $attrValidatorHelper->validateLocale($refDesigners, 'en_US')->shouldBeCalled();
        $attrValidatorHelper->validateLocale($refDesigners, 'fr_FR')->shouldBeCalled();
        $attrValidatorHelper->validateScope($refDesigners, 'mobile')->shouldBeCalled();
        $attrValidatorHelper->validateScope($refDesigners, 'ecommerce')->shouldBeCalled();

        $product->getValue('ref_designer', 'en_US', 'mobile')->willReturn(
            ReferenceEntityCollectionValue::scopableLocalizableValue(
                'ref_designers',
                [RecordCode::fromString('starck'), RecordCode::fromString('dyson')],
                'mobile',
                'en_US'
            )
        );
        $builder->addOrReplaceValue($product, $refDesigners, 'fr_FR', 'ecommerce', ['starck', 'dyson'])->shouldBeCalled();

        $this->copyAttributeData(
            $product,
            $product,
            $refDesigners,
            $refDesigners,
            $options
        );
    }
}
