<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Mapping;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;

class DelegatingClassMetadataFactorySpec extends ObjectBehavior
{
    function let(
        MetadataFactoryInterface $dupond,
        MetadataFactoryInterface $dupont
    ) {
        $this->addMetadataFactory($dupond);
        $this->addMetadataFactory($dupont);
    }

    function it_is_a_validator_metadata_factory()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\MetadataFactoryInterface');
    }

    function it_delegate_metadata_creation_to_the_first_factory_having_one_for_an_object(
        $object,
        $dupond,
        $dupont,
        ClassMetadata $metadata
    ) {
        $dupond->hasMetadataFor($object)->willReturn(false);
        $dupont->hasMetadataFor($object)->willReturn(true);
        $dupont->getMetadataFor($object)->willReturn($metadata);

        $this->getMetadataFor($object)->shouldReturn($metadata);
    }

   function its_getMetadataFor_method_throws_exception_when_no_factory_has_metadata_for_object(
        $object,
        $dupond,
        $dupont
    ) {
        $dupond->hasMetadataFor($object)->willReturn(false);
        $dupont->hasMetadataFor($object)->willReturn(false);

        $this
            ->shouldThrow(new NoSuchMetadataException())
            ->duringGetMetadataFor($object);
   }

    function it_has_metadata_if_at_least_one_factory_has_one(
        $object,
        $dupond,
        $dupont
    ) {
        $dupond->hasMetadataFor($object)->willReturn(false);
        $dupont->hasMetadataFor($object)->willReturn(true);

        $this->hasMetadataFor($object)->shouldReturn(true);
    }

    function it_does_not_have_metadata_if_no_factory_has_one(
        $object,
        $dupond,
        $dupont
    ) {
        $dupond->hasMetadataFor($object)->willReturn(false);
        $dupont->hasMetadataFor($object)->willReturn(false);

        $this->hasMetadataFor($object)->shouldReturn(false);
    }
}
