<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class ImagePresenterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        UrlGeneratorInterface $generator,
        FileInfoRepositoryInterface $repository
    ) {
        $this->beConstructedWith($attributeRepository, $generator, $repository);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_media() {
        $this->supports('pim_catalog_image')->shouldBe(true);
    }

    function it_does_not_presents_original_if_original_is_empty(ValueInterface $value)
    {
        $this
            ->present(null, ['data' => 'key/of/the/change.jpg'])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_does_not_presents_new_if_new_is_empty(FileInfoInterface $media)
    {
        $this
            ->present($media, ['data' => null])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_presents_image(
        $generator,
        FileInfoInterface $media,
        FileInfoInterface $changedMedia,
        FileInfoRepositoryInterface $repository
    ) {
        $repository->findOneByIdentifier('key/of/the/changed/media.jpg')->willReturn($changedMedia);
        $changedMedia->getKey()->willReturn('key/of/the/changed/media.jpg');
        $changedMedia->getHash()->willReturn('different_hash');
        $changedMedia->getOriginalFilename()->willReturn('changed_media.jpg');

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
            ->present($media, ['data' => 'key/of/the/changed/media.jpg'])
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
