<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueComplete;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProductValueCompleteValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueCompleteValidator');
    }

    function it_validates_simple_string(
        $context,
        ProductValueComplete $constraint
    ) {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate('simple value', $constraint);
    }

    function it_does_not_validate_nullable_value(
        $context,
        ProductValueComplete $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context
            ->buildViolation($constraint->messageNotNull)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(null, $constraint);
    }

    function it_does_not_validate_false_value(
        $context,
        ProductValueComplete $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context
            ->buildViolation($constraint->messageNotNull)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate(false, $constraint);
    }

    function it_does_not_validate_an_empty_collection(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $productValue->getData()->willReturn(new ArrayCollection());

        $context
            ->buildViolation($constraint->messageComplete)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($productValue, $constraint);
    }

    function it_validates_a_product_value_with_backendtype_as_prices(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        ProductPriceInterface $productPrice,
        AttributeInterface $attribute
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $productPrice->getCurrency()->willReturn('EUR');
        $productPrice->getData()->willReturn(15);
        $productValue->getData()->willReturn([$productPrice]);

        $attribute->getBackendType()->willReturn('prices');
        $productValue->getAttribute()->willReturn($attribute);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_does_not_validate_a_product_value_with_backendtype_as_prices(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        ProductPriceInterface $productPrice,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $productPrice->getCurrency()->willReturn('EUR');
        $productPrice->getData()->willReturn(null);
        $productValue->getData()->willReturn([$productPrice]);

        $attribute->getBackendType()->willReturn('prices');
        $productValue->getAttribute()->willReturn($attribute);

        $context
            ->buildViolation($constraint->messageComplete)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($productValue, $constraint);
    }

    function it_validates_a_product_value_with_backendtype_as_media(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $productMedia = new ProductMedia();
        $productMedia->setFilename('akeneo.jpg');
        $productValue->getMedia()->willReturn($productMedia);
        $productValue->getData()->willReturn('data');

        $attribute->getBackendType()->willReturn('media');
        $productValue->getAttribute()->willReturn($attribute);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_does_not_validate_a_product_value_with_backendtype_as_media(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $productMedia = new ProductMedia();
        $productValue->getMedia()->willReturn($productMedia);
        $productValue->getData()->willReturn('data');

        $attribute->getBackendType()->willReturn('media');
        $productValue->getAttribute()->willReturn($attribute);

        $context
            ->buildViolation($constraint->messageComplete)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($productValue, $constraint);
    }

    function it_validates_a_product_value_with_backendtype_as_metric(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $metric = new Metric();
        $metric->setData('data');
        $productValue->getMetric()->willReturn($metric);
        $productValue->getData()->willReturn('data');

        $attribute->getBackendType()->willReturn('metric');
        $productValue->getAttribute()->willReturn($attribute);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_does_not_validate_a_product_value_with_backendtype_as_metric(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $metric = new Metric();
        $productValue->getMetric()->willReturn($metric);
        $productValue->getData()->willReturn('data');

        $attribute->getBackendType()->willReturn('metric');
        $productValue->getAttribute()->willReturn($attribute);

        $context
            ->buildViolation($constraint->messageComplete)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($productValue, $constraint);
    }

    function it_returns_null_when_unknown_backendtype(
        $context,
        ProductValueComplete $constraint,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $constraint->getChannel()->willReturn($this->getChannel());

        $metric = new Metric();
        $productValue->getMetric()->willReturn($metric);
        $productValue->getData()->willReturn('data');

        $attribute->getBackendType()->willReturn('unknown_metric');
        $productValue->getAttribute()->willReturn($attribute);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }

    function getChannel()
    {
        $channel = new Channel();
        $channel->setCode('catalog');
        $currency = new Currency();
        $currency->setCode('EUR');
        $channel->addCurrency($currency);

        return $channel;
    }
}
