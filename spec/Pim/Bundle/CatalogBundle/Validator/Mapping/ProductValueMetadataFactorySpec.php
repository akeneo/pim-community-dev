<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Mapping;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Pim\Bundle\CatalogBundle\Validator\Mapping\ClassMetadataFactory;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;

class ProductValueMetadataFactorySpec extends ObjectBehavior
{
    function it_is_a_validator_metadata_factory()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\MetadataFactoryInterface');
    }

    function let(
        ConstraintGuesserInterface $guesser,
        ClassMetadataFactory $factory
    ) {
        $this->beConstructedWith($guesser, $factory);
    }

    function it_provides_metadata_for_product_value(
        $guesser,
        $factory,
        ClassMetadata $metadata,
        AbstractProductValue $value,
        AbstractAttribute $attribute,
        Constraint $foo,
        Constraint $bar
    ) {
        $factory->createMetadata(Argument::any())->willReturn($metadata);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn('varchar');
        $guesser->guessConstraints($attribute)->willReturn([$foo, $bar]);

        $metadata->addPropertyConstraint('varchar', $foo)->shouldBeCalled();
        $metadata->addPropertyConstraint('varchar', $bar)->shouldBeCalled();

        $this->getMetadataFor($value);
    }

    function its_getMetadataFor_method_throws_exception_when_argument_is_not_an_AbstractProductValue($object)
    {
        $this
            ->shouldThrow(new NoSuchMetadataException())
            ->duringGetMetadataFor($object);
    }

    function it_has_metadata_for_AbstractProductValue(AbstractProductValue $value)
    {
        $this->hasMetadataFor($value)->shouldBe(true);
    }

    function it_does_not_have_metadata_for_something_else($object)
    {
        $this->hasMetadataFor($object)->shouldBe(false);
    }
}
