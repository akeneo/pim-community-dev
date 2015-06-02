<?php

namespace spec\PimEnterprise\Component\ProductAsset\Builder;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Builder\MetadataBuilderInterface;

class MetadataBuilderRegistrySpec extends ObjectBehavior
{
    function let(MetadataBuilderInterface $imageMetaBuilder, MetadataBuilderInterface $fileMetaBuilder)
    {
    }

    function it_registers_builders($imageMetaBuilder, $fileMetaBuilder)
    {
        $this->all()->shouldReturn([]);

        $this->register($imageMetaBuilder, 'pimee_image_meta_builder');
        $this->register($fileMetaBuilder, 'pimee_file_meta_builder');

        $this->all()->shouldReturn([
            'pimee_image_meta_builder' => $imageMetaBuilder,
            'pimee_file_meta_builder' => $fileMetaBuilder
        ]);
    }

    function it_throws_an_exception_if_a_builder_is_already_registered($imageMetaBuilder, $fileMetaBuilder)
    {
        $this->register($imageMetaBuilder, 'pimee_image_meta_builder');
        $this->register($fileMetaBuilder, 'pimee_file_meta_builder');

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Exception\AlreadyRegisteredMetadataBuilderException')
            ->during('register', [$imageMetaBuilder, 'pimee_image_meta_builder']);
    }

    function it_returns_a_builder_by_its_alias($imageMetaBuilder, $fileMetaBuilder)
    {
        $this->register($imageMetaBuilder, 'pimee_image_meta_builder');
        $this->register($fileMetaBuilder, 'pimee_file_meta_builder');

        $this->get('pimee_image_meta_builder')->shouldReturn($imageMetaBuilder);
    }

    function it_throws_an_exception_if_a_builder_cant_be_found($imageMetaBuilder)
    {
        $this->register($imageMetaBuilder, 'pimee_image_meta_builder');

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Exception\NonRegisteredMetadataBuilderException')
            ->during('get', ['pimee_file_meta_builder']);
    }

    function it_returns_the_existence_of_a_builder($imageMetaBuilder)
    {
        $this->register($imageMetaBuilder, 'pimee_image_meta_builder');

        $this->has('pimee_image_meta_builder')->shouldReturn(true);
        $this->has('pimee_file_meta_builder')->shouldReturn(false);
    }
}
