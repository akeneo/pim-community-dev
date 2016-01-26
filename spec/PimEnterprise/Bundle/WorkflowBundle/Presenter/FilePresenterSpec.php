<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilePresenterSpec extends ObjectBehavior
{
    function let(UrlGeneratorInterface $generator)
    {
        $this->beConstructedWith($generator);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_file(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_file');
        $value->getAttribute()->willReturn($attribute);

        $this->supports($value)->shouldBe(true);
    }

    function it_does_not_presents_original_if_original_is_empty(
        ProductValueInterface $value
    ) {
        $value->getMedia()->willReturn(null);

        $this
            ->present($value, [
                'data' => [
                    'filePath' => 'key/of/the/change.jpg',
                    'originalFilename' => 'change_bar.jpg',
                ]
            ])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_does_not_presents_new_if_new_is_empty(
        ProductValueInterface $value,
        FileInfoInterface $media
    ) {
        $value->getMedia()->willReturn($media);

        $this
            ->present($value, ['data' => null])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_presents_file(
        $generator,
        ProductValueInterface $value,
        FileInfoInterface $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/media.jpg');
        $media->getHash()->willReturn('key/of/the/media.jpg');
        $media->getOriginalFilename()->willReturn('original_foo.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/media.jpg')]
            )
            ->willReturn('url/of/the/media.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/change.jpg')]
            )
            ->willReturn('url/of/the/change.jpg');

        $this
            ->present($value, [
                'data' => [
                    'filePath' => 'key/of/the/change.jpg',
                    'originalFilename' => 'change_bar.jpg',
                ]
            ])
            ->shouldReturn([
                'before' => sprintf(
                    '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
                    'url/of/the/media.jpg',
                    'original_foo.jpg'
                ),
                'after' => sprintf(
                    '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
                    'url/of/the/change.jpg',
                    'change_bar.jpg'
                )
            ]);
    }
}
