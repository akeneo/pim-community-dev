<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Mapping;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

class DelegatingClassMetadataFactorySpec extends ObjectBehavior
{
    function let(
        MetadataFactoryInterface $customFactory,
        MetadataFactoryInterface $defaultFactory
    ) {
        $this->addMetadataFactory($customFactory);
        $this->addMetadataFactory($defaultFactory);
    }

    function it_is_a_validator_metadata_factory()
    {
        $this->shouldBeAnInstanceOf(MetadataFactoryInterface::class);
    }

    function it_delegate_metadata_creation_to_the_first_factory_having_one_for_an_object(
        $object,
        $customFactory,
        $defaultFactory,
        ClassMetadata $metadata
    ) {
        $customFactory->hasMetadataFor($object)->willReturn(false);
        $defaultFactory->hasMetadataFor($object)->willReturn(true);
        $defaultFactory->getMetadataFor($object)->willReturn($metadata);

        $this->getMetadataFor($object)->shouldReturn($metadata);
    }

    function its_getMetadataFor_method_throws_exception_when_no_factory_has_metadata_for_object(
        $object,
        $customFactory,
        $defaultFactory
    ) {
        $customFactory->hasMetadataFor($object)->willReturn(false);
        $defaultFactory->hasMetadataFor($object)->willReturn(false);

        $this
            ->shouldThrow(new NoSuchMetadataException())
            ->duringGetMetadataFor($object);
    }

    function it_has_metadata_if_at_least_one_factory_has_one(
        $object,
        $customFactory,
        $defaultFactory
    ) {
        $customFactory->hasMetadataFor($object)->willReturn(false);
        $defaultFactory->hasMetadataFor($object)->willReturn(true);

        $this->hasMetadataFor($object)->shouldReturn(true);
    }

    function it_does_not_have_metadata_if_no_factory_has_one(
        $object,
        $customFactory,
        $defaultFactory
    ) {
        $customFactory->hasMetadataFor($object)->willReturn(false);
        $defaultFactory->hasMetadataFor($object)->willReturn(false);

        $this->hasMetadataFor($object)->shouldReturn(false);
    }
}
