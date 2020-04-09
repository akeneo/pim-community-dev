<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate\GetOperandValue;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate\UpdateNumericValue;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\CalculateActionApplier;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CalculateActionApplierSpec extends ObjectBehavior
{
    function let(
        GetOperandValue $getOperandValue,
        UpdateNumericValue $updateValue
    )
    {
        $this->beConstructedWith($getOperandValue, $updateValue);
    }

    function it_is_an_action_applier()
    {
        $this->shouldImplement(ActionApplierInterface::class);
    }

    function it_is_a_calculate_action_aplier()
    {
        $this->shouldHaveType(CalculateActionApplier::class);
    }

    function it_only_supports_calculate_actions(
        ProductCalculateActionInterface $calculateAction,
        ActionInterface $otherAction
    ) {
        $this->supports($calculateAction)->shouldReturn(true);
        $this->supports($otherAction)->shouldReturn(false);
    }

    function it_does_nothing_on_products_without_family(UpdateNumericValue $updateValue)
    {
        $product = new Product();
        $updateValue->forEntity($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_does_nothing_if_destination_field_does_not_belong_to_family(UpdateNumericValue $updateValue)
    {
        $family = new Family();
        $product = (new Product())->setFamily($family);

        $updateValue->forEntity($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_does_nothing_if_attribute_is_not_on_same_variation_level_as_entity(
        UpdateNumericValue $updateValue,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $product
    ) {
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $familyVariant->getLevelForAttributeCode('ratio_fr_en')->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $product->getVariationLevel()->willReturn(2);

        $updateValue->forEntity($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_skips_the_entity_if_one_of_the_operation_values_is_null(
        GetOperandValue $getOperandValue,
        UpdateNumericValue $updateValue,
        FamilyInterface $family
    ) {
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $family->getId()->willReturn(42);
        $product = (new Product())->setFamily($family->getWrappedObject());

        $action = $this->productCalculateAction();
        $getOperandValue->fromEntity($product, $action->getSource())->willReturn(null);

        $updateValue->forEntity($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_skips_the_entity_if_ther_is_a_division_by_zero_operation(
        GetOperandValue $getOperandValue,
        UpdateNumericValue $updateValue,
        FamilyInterface $family
    ) {
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $family->getId()->willReturn(42);
        $product = (new Product())->setFamily($family->getWrappedObject());

        $action = $this->productCalculateAction();
        $getOperandValue->fromEntity($product, $action->getSource())->willReturn(5.0);
        $divideOperation = $action->getOperationList()->getIterator()->getArrayCopy()[0];
        $getOperandValue->fromEntity($product, $divideOperation->getOperand())->willReturn(0.0);

        $updateValue->forEntity($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_calculates_a_value(
        GetOperandValue $getOperandValue,
        UpdateNumericValue $updateValue,
        FamilyInterface $family
    ) {
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $family->getId()->willReturn(42);
        $product = (new Product())->setFamily($family->getWrappedObject());

        $action = $this->productCalculateAction();
        $getOperandValue->fromEntity($product, Argument::type(Operand::class))
            ->shouldBeCalledTimes(3)
            ->willReturn(5.0, 2.0, 10.0);

        $updateValue->forEntity($product, $action->getDestination(), 260.0)->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    private function productCalculateAction(bool $destinationIsPrice = true): ProductCalculateActionInterface
    {
        $destination = [
            'field' => 'ratio_fr_en',
        ];
        if (true === $destinationIsPrice) {
            $destination['currency'] = 'EUR';
        }

        return new ProductCalculateAction(
            [
                'destination' => $destination,
                'source' => ['field' => 'total', 'locale' => 'fr_FR'],
                'operation_list' => [
                    [
                        'operator' => 'divide',
                        'field' => 'total',
                        'locale' => 'en_US',
                    ],
                    [
                        'operator' => 'multiply',
                        'value' => 100,
                    ],
                    [
                        'operator' => 'add',
                        'field' => 'base_price',
                        'currency' => 'EUR',
                    ],
                ],
            ]
        );
    }
}
