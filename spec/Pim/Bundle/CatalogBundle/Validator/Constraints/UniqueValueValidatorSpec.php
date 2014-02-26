<?php                                                                           

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;

class UniqueValueValidatorSpec extends ObjectBehavior
{
    const PROPERTY_PATH='children[values].children[sku].children[varchar].data';

    function let(
        ProductManager $productManager,
        ExecutionContextInterface $context,
        Form $form,
        ProductInterface $product,
        ProductValueInterface $value
    ) {
        $this->beConstructedWith($productManager);

        $product->getValue('sku')->willReturn($value);

        $form->getData()->willReturn($product);

        $context->getPropertyPath()->willReturn(self::PROPERTY_PATH);
        $context->getRoot()->willReturn($form);

        $this->initialize($context);
    }

    function it_validates_unique_value(
        ProductManager $productManager,
        ProductValueInterface $value,
        ExecutionContextInterface $context,
        UniqueValue $constraint
    ) {
        $productManager->valueExists($value)->willReturn(false);
        $this->validate("my_value", $constraint)->shouldReturn(null);
        $context->addViolation()->shouldNotBeCalled();
    }

    function it_adds_violation_with_non_unique_value(
        ProductManager $productManager,
        ProductValueInterface $value,
        ExecutionContextInterface $context,
        UniqueValue $constraint
    ) {
        $productManager->valueExists($value)->willReturn(true);
        $context->addViolation($constraint->message)->shouldBeCalled();
        $this->validate("my_value", $constraint)->shouldReturn(null);
    }

    function it_does_not_validate_with_non_context(ExecutionContextInterface $emptyContext, Constraint $constraint)
    {
        $this->initialize($emptyContext);
        $this->validate("my_value", $constraint)->shouldReturn(null);
        $emptyContext->addViolation()->shouldNotBeCalled();
    }
}

