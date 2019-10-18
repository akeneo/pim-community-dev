<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class FilePresenterSpec extends ObjectBehavior
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

    function it_supports_file() {
        $this->supports('pim_catalog_file')->shouldBe(true);
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

    function it_presents_file(
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
        $media->getHash()->willReturn('hash');
        $media->getOriginalFilename()->willReturn('media.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/original/media.jpg')]
            )
            ->willReturn('url/of/the/original/media.jpg');

        $generator
            ->generate(
                'pim_enrich_media_show',
                ['filename' => urlencode('key/of/the/changed/media.jpg')]
            )
            ->willReturn('url/of/the/changed/media.jpg');

        $this
            ->present($value, ['data' => 'key/of/the/changed/media.jpg'])
            ->shouldReturn([
                'before' => sprintf(
                    '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
                    'url/of/the/original/media.jpg',
                    'media.jpg'
                ),
                'after' => sprintf(
                    '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
                    'url/of/the/changed/media.jpg',
                    'changed_media.jpg'
                )
            ]);
    }
}
