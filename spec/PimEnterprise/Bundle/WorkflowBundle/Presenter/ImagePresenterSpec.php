<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImagePresenterSpec extends ObjectBehavior
{
    function let(UrlGeneratorInterface $generator, FileInfoRepositoryInterface $repository)
    {
        $this->beConstructedWith($generator, $repository);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_media(
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_image');
        $value->getAttribute()->willReturn($attribute);

        $this->supports($value)->shouldBe(true);
    }

    function it_does_not_presents_original_if_original_is_empty(
        ValueInterface $value
    ) {
        $value->getData()->willReturn(null);

        $this
            ->present($value, ['data' => 'key/of/the/change.jpg'])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_does_not_presents_new_if_new_is_empty(
        ValueInterface $value,
        FileInfoInterface $media
    ) {
        $value->getData()->willReturn($media);

        $this
            ->present($value, ['data' => null])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_presents_image(
        $generator,
        ValueInterface $value,
        FileInfoInterface $media,
        FileInfoInterface $changedMedia,
        FileInfoRepositoryInterface $repository
    ) {
        $repository->findOneByIdentifier('key/of/the/changed/media.jpg')->willReturn($changedMedia);
        $changedMedia->getKey()->willReturn('key/of/the/changed/media.jpg');
        $changedMedia->getHash()->willReturn('different_hash');
        $changedMedia->getOriginalFilename()->willReturn('changed_media.jpg');

        $value->getData()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/original/media.jpg');
        $media->getHash()->willReturn('url/of/the/original/media.jpg');
        $media->getOriginalFilename()->willReturn('media.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/original/media.jpg'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/original/media.jpg');
        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/changed/media.jpg'), 'filter' => 'thumbnail']
            )
            ->willReturn('url/of/the/changed/media.jpg');

        $this
            ->present($value, ['data' => 'key/of/the/changed/media.jpg'])
            ->shouldReturn([
                'before' => sprintf(
                    '<img src="%s" title="%s" />',
                    'url/of/the/original/media.jpg',
                    'media.jpg'),
                'after' => sprintf('<img src="%s" title="%s" />',
                    'url/of/the/changed/media.jpg',
                    'changed_media.jpg')
            ]);
    }
}
