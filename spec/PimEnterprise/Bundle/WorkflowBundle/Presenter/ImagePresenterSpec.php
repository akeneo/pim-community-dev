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

    function it_supports_media(
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $value->getAttribute()->willReturn($attribute);

        $this->supports($value)->shouldBe(true);
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
            ->present($value, ['value' => ['filename' => 'bar.jpg', 'originalFilename' => 'original_bar.jpg']])
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

        $this->present($value, ['value' => []])->shouldReturn(
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
            ->present($value, ['value' => ['filename' => 'bar.jpg', 'originalFilename' => 'original_bar.jpg']])
            ->shouldReturn(
                '<ul class="diff">' .
                    '<li class="changed file"><img src="/media/bar.jpg" title="original_bar.jpg" /></li>' .
                '</ul>'
            );
    }
}
