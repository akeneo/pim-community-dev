<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Copier\TableAttributeCopier;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;

class TableAttributeCopierSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith($entityWithValuesBuilder, $attrValidatorHelper);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(AttributeCopierInterface::class);
        $this->shouldHaveType(TableAttributeCopier::class);
    }

    function it_only_supports_table_attributes_as_source_and_destination(
        AttributeInterface $table,
        AttributeInterface $name
    ) {
        $table->getType()->willReturn(AttributeTypes::TABLE);
        $table->getCode()->willReturn('packaging');
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $name->getCode()->willReturn('name');

        $this->supportsAttributes($table, $name)->shouldBe(false);
        $this->supportsAttributes($name, $table)->shouldBe(false);
        $this->supportsAttributes($name, $name)->shouldBe(false);
        $this->supportsAttributes($table, $table)->shouldBe(true);
    }

    function it_can_only_copy_data_if_the_source_and_destination_attributes_are_the_same(
        AttributeInterface $nutritionalInfo,
        AttributeInterface $packaging
    ) {
        $nutritionalInfo->getType()->willReturn(AttributeTypes::TABLE);
        $nutritionalInfo->getCode()->willReturn('nutritional_info');
        $packaging->getType()->willReturn(AttributeTypes::TABLE);
        $packaging->getCode()->willReturn('packaging');

        $this->supportsAttributes($nutritionalInfo, $packaging)->shouldBe(false);
        $this->supportsAttributes($packaging, $nutritionalInfo)->shouldBe(false);
        $this->supportsAttributes($nutritionalInfo, $nutritionalInfo)->shouldBe(true);
        $this->supportsAttributes($packaging, $packaging)->shouldBe(true);
    }

    function it_copies_a_table_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        AttributeInterface $attribute,
        EntityWithValuesInterface $fromProduct,
        EntityWithValuesInterface $toProduct
    ) {
        $attribute->getCode()->willReturn('nutritional_info');
        $fromProduct->getValue('nutritional_info', 'en_US', null)->willReturn(TableValue::localizableValue(
            'nutritional_info',
            Table::fromNormalized([
                ['ingredient' => 'sugar', 'quantity' => 10],
                ['ingredient' => 'flour', 'quantity' => 5, 'is_allergenic' => false],
                ['ingredient' => 'nuts', 'quantity' => 5, 'is_allergenic' => true],
            ]),
            'en_US'
        ));

        $attrValidatorHelper->validateLocale($attribute, 'en_US')->shouldBeCalledOnce();
        $attrValidatorHelper->validateLocale($attribute, 'fr_FR')->shouldBeCalledOnce();
        $attrValidatorHelper->validateScope($attribute, null)->shouldBeCalledTimes(2);
        $entityWithValuesBuilder->addOrReplaceValue($toProduct, $attribute, 'fr_FR', null,
            [
                ['ingredient' => 'sugar', 'quantity' => 10],
                ['ingredient' => 'flour', 'quantity' => 5, 'is_allergenic' => false],
                ['ingredient' => 'nuts', 'quantity' => 5, 'is_allergenic' => true],
            ]
        )->shouldBeCalled();

        $this->copyAttributeData(
            $fromProduct,
            $toProduct,
            $attribute,
            $attribute,
            ['from_locale' => 'en_US', 'to_locale' => 'fr_FR']
        );
    }

    function it_copies_an_empty_table_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        AttributeInterface $attribute,
        EntityWithValuesInterface $fromProduct,
        EntityWithValuesInterface $toProduct
    ) {
        $attribute->getCode()->willReturn('nutritional_info');
        $fromProduct->getValue('nutritional_info', 'en_US', 'ecommerce')->willReturn(null);

        $attrValidatorHelper->validateLocale($attribute, 'en_US')->shouldBeCalledOnce();
        $attrValidatorHelper->validateLocale($attribute, 'fr_FR')->shouldBeCalledOnce();
        $attrValidatorHelper->validateScope($attribute, 'ecommerce')->shouldBeCalledOnce();
        $attrValidatorHelper->validateScope($attribute, 'mobile')->shouldBeCalledOnce();

        $entityWithValuesBuilder->addOrReplaceValue($toProduct, $attribute, 'fr_FR', 'mobile', null)->shouldBeCalled();

        $this->copyAttributeData(
            $fromProduct,
            $toProduct,
            $attribute,
            $attribute,
            ['from_locale' => 'en_US', 'from_scope' => 'ecommerce', 'to_locale' => 'fr_FR', 'to_scope' => 'mobile'],
        );
    }
}
