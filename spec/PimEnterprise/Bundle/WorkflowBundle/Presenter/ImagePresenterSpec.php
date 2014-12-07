<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImagePresenterSpec extends ObjectBehavior
{
    function let(UrlGeneratorInterface $generator)
    {
        $this->beConstructedWith($generator);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_value_which_stores_data_in_the_media_property_and_have_an_old_image_and_a_new_one(
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute,
        Model\ProductMedia $media
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn('media');
        $value->getMedia()->willReturn($media);
        $media->getMimeType()->willReturn('image/png');

        $this->supports($value, ['media' => ['mimeType' => 'image/jpeg']])->shouldBe(true);
    }

    function it_supports_value_which_stores_data_in_the_media_property_and_have_only_an_old_image(
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute,
        Model\ProductMedia $media
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn('media');
        $value->getMedia()->willReturn($media);
        $media->getMimeType()->willReturn('image/png');

        $this->supports($value, ['media' => []])->shouldBe(true);
    }

    function it_supports_value_which_stores_data_in_the_media_property_and_have_only_a_new_image(
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn('media');
        $value->getMedia()->willReturn(null);

        $this->supports($value, ['media' => ['mimeType' => 'image/jpeg']])->shouldBe(true);
    }

    function it_presents_old_and_new_images_side_by_side(
        $generator,
        Model\ProductValueInterface $value,
        Model\ProductMedia $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getFilename()->willReturn('foo.jpg');
        $media->getOriginalFilename()->willReturn('original_foo.jpg');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'foo.jpg', 'filter' => 'thumbnail'])
            ->willReturn('/media/foo.jpg');
        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'bar.jpg', 'filter' => 'thumbnail'])
            ->willReturn('/media/bar.jpg');

        $this
            ->present($value, ['media' => ['filename' => 'bar.jpg', 'originalFilename' => 'original_bar.jpg']])
            ->shouldReturn(
                '<ul class="diff">' .
                    '<li class="base file"><img src="/media/foo.jpg" title="original_foo.jpg" /></li>' .
                    '<li class="changed file"><img src="/media/bar.jpg" title="original_bar.jpg" /></li>' .
                '</ul>'
            );
    }

    function it_presents_only_old_image_if_no_new_one_is_provided(
        $generator,
        Model\ProductValueInterface $value,
        Model\ProductMedia $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getFilename()->willReturn('foo.jpg');
        $media->getOriginalFilename()->willReturn('original_foo.jpg');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'foo.jpg', 'filter' => 'thumbnail'])
            ->willReturn('/media/foo.jpg');

        $this->present($value, ['media' => []])->shouldReturn(
            '<ul class="diff">' .
                '<li class="base file"><img src="/media/foo.jpg" title="original_foo.jpg" /></li>' .
            '</ul>'
        );
    }

    function it_presents_only_new_image_if_there_is_no_old_one(
        $generator,
        Model\ProductValueInterface $value
    ) {
        $value->getMedia()->willReturn(null);

        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'bar.jpg', 'filter' => 'thumbnail'])
            ->willReturn('/media/bar.jpg');

        $this
            ->present($value, ['media' => ['filename' => 'bar.jpg', 'originalFilename' => 'original_bar.jpg']])
            ->shouldReturn(
                '<ul class="diff">' .
                    '<li class="changed file"><img src="/media/bar.jpg" title="original_bar.jpg" /></li>' .
                '</ul>'
            );
    }
}
