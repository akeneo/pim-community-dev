<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilePresenterSpec extends ObjectBehavior
{
    function let(
        UrlGeneratorInterface $generator,
        FileInfoRepositoryInterface $repository
    ) {
        $this->beConstructedWith($generator, $repository);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_file() {
        $this->supports('pim_catalog_file')->shouldBe(true);
    }

    function it_does_not_presents_original_if_original_is_empty()
    {
        $this
            ->present(null, ['data' => 'key/of/the/change.pdf'])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_does_not_presents_new_if_new_is_empty(FileInfoInterface $media) {
        $this
            ->present($media, ['data' => null])
            ->shouldReturn(['before' => '', 'after' => '']);
    }

    function it_presents_file(
        $generator,
        FileInfoInterface $media,
        FileInfoInterface $changedMedia,
        FileInfoRepositoryInterface $repository
    ) {
        $repository->findOneByIdentifier('key/of/the/changed/file.pdf')->willReturn($changedMedia);
        $changedMedia->getKey()->willReturn('key/of/the/changed/file.pdf');
        $changedMedia->getHash()->willReturn('different_hash');
        $changedMedia->getOriginalFilename()->willReturn('changed_file.pdf');

        $media->getKey()->willReturn('key/of/the/original/file.pdf');
        $media->getHash()->willReturn('hash');
        $media->getOriginalFilename()->willReturn('file.pdf');

        $generator
            ->generate(
                'pim_enrich_media_download',
                ['filename' => urlencode('key/of/the/original/file.pdf')]
            )
            ->willReturn('url/of/the/original/file.pdf');

        $generator
            ->generate(
                'pim_enrich_media_download',
                ['filename' => urlencode('key/of/the/changed/file.pdf')]
            )
            ->willReturn('url/of/the/changed/file.pdf');

        $this
            ->present($media, ['data' => 'key/of/the/changed/file.pdf'])
            ->shouldReturn([
                'before' => sprintf(
                    '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
                    'url/of/the/original/file.pdf',
                    'file.pdf'
                ),
                'after' => sprintf(
                    '<i class="icon-file"></i><a target="_blank" class="no-hash" href="%s">%s</a>',
                    'url/of/the/changed/file.pdf',
                    'changed_file.pdf'
                )
            ]);
    }
}
