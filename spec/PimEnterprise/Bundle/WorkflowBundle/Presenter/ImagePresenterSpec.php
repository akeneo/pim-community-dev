<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
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
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $value->getAttribute()->willReturn($attribute);

        $this->supports($value)->shouldBe(true);
    }

    function it_does_not_presents_original_if_original_is_empty(
        ProductValueInterface $value
    ) {
        $value->getMedia()->willReturn(null);

        $this
            ->presentOriginal($value, [
                'data' => [
                    'filePath' => 'key/of/the/change.jpg',
                    'originalFilename' => 'change_bar.jpg',
                ]
            ])
            ->shouldReturn('');
    }

    function it_does_not_presents_new_if_new_is_empty(
        ProductValueInterface $value,
        FileInfoInterface $media
    ) {
        $value->getMedia()->willReturn($media);

        $this
            ->presentNew($value, ['data' => null])
            ->shouldReturn('');
    }

    function it_presents_original_image(
        $generator,
        ProductValueInterface $value,
        FileInfoInterface $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/media.jpg');
        $media->getOriginalFilename()->willReturn('original_foo.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/media.jpg'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/media.jpg');

        $this
            ->presentOriginal($value, [
                'data' => [
                    'filePath' => 'key/of/the/change.jpg',
                    'originalFilename' => 'change_bar.jpg',
                ]
            ])
            ->shouldReturn(sprintf(
                '<img src="%s" title="original_foo.jpg" />',
                'url/of/the/media.jpg'
            ));
    }

    function it_presents_new_image(
        $generator,
        ProductValueInterface $value,
        FileInfoInterface $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/media.jpg');
        $media->getOriginalFilename()->willReturn('original_foo.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/change.jpg'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/media.jpg');

        $this
            ->presentNew($value, [
                'data' => [
                    'filePath' => 'key/of/the/change.jpg',
                    'originalFilename' => 'change_bar.jpg',
                ]
            ])
            ->shouldReturn(sprintf(
                '<img src="%s" title="change_bar.jpg" />',
                'url/of/the/media.jpg'
            ));
    }
}
