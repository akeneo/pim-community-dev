<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
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

    function it_presents_old_and_new_images_side_by_side(
        $generator,
        ProductValueInterface $value,
        FileInterface $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/media.jpg');
        $media->getHash()->willReturn('54qsfda8e7r54f');
        $media->getOriginalFilename()->willReturn('original_foo.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/media.jpg'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/media.jpg');
        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/change.jpg'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/change.jpg');

        $change = [
            'data' => [
                'hash' => '98az7er654ert4s',
                'filePath' => 'key/of/the/change.jpg',
                'originalFilename' => 'change_bar.jpg',
            ]
        ];

        $this
            ->present($value, $change)
            ->shouldReturn(sprintf(
                '<ul class="diff">' .
                '<li class="base file"><img src="%s" title="original_foo.jpg" /></li>' .
                '<li class="changed file"><img src="%s" title="change_bar.jpg" /></li>' .
                '</ul>',
                'url/of/the/media.jpg',
                'url/of/the/change.jpg'
            ));
    }

    function it_presents_only_old_image_if_no_new_one_is_provided(
        $generator,
        ProductValueInterface $value,
        FileInterface $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/media.jpg');
        $media->getHash()->willReturn('54qsfda8e7r54f');
        $media->getOriginalFilename()->willReturn('original_foo.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/media.jpg'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/media.jpg');

        $this->present($value, ['data' => []])->shouldReturn(
            '<ul class="diff">' .
            '<li class="base file"><img src="url/of/the/media.jpg" title="original_foo.jpg" /></li>' .
            '</ul>'
        );
    }

    function it_presents_only_new_image_if_there_is_no_old_one(
        $generator,
        ProductValueInterface $value
    ) {
        $value->getMedia()->willReturn(null);

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/change.png'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/change.png');

        $change = [
            'data' => [
                'hash'             => '98az7er654ert4s',
                'filePath'         => 'key/of/the/change.png',
                'originalFilename' => 'change_foo.png',
            ]
        ];

        $this
            ->present($value, $change)
            ->shouldReturn(
                '<ul class="diff">' .
                    '<li class="changed file"><img src="url/of/the/change.png" title="change_foo.png" /></li>' .
                '</ul>'
            );
    }
}
