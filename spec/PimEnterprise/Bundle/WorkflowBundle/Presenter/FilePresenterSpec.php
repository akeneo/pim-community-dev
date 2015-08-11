<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
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

    function it_presents_files_in_a_list(
        $generator,
        ProductValueInterface $value,
        FileInterface $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/media.pdf');
        $media->getHash()->willReturn('54qsfda8e7r54f');
        $media->getOriginalFilename()->willReturn('original_foo.pdf');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => urlencode('key/of/the/media.pdf')])
            ->willReturn('url/of/the/media.pdf');
        $generator
            ->generate('pim_enrich_media_show', ['filename' => urlencode('key/of/the/change.txt')])
            ->willReturn('url/of/the/change.txt');

        $change = [
            'data' => [
                'hash' => '98az7er654ert4s',
                'filePath' => 'key/of/the/change.txt',
                'originalFilename' => 'the new change.txt',
            ]
        ];

        $this->present($value, $change)->shouldReturn(sprintf(
            '<ul class="diff">' .
                '<li class="base file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="%s">original_foo.pdf</a>' .
                '</li>' .
                '<li class="changed file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="%s">the new change.txt</a>' .
                '</li>' .
            '</ul>',
            'url/of/the/media.pdf',
            'url/of/the/change.txt'
        ));
    }

    function it_only_presents_new_file_if_value_does_not_has_a_media_yet(
        $generator,
        ProductValueInterface $value
    ) {
        $value->getMedia()->willReturn(null);

        $generator
            ->generate('pim_enrich_media_show', ['filename' => urlencode('key/of/the/change.txt')])
            ->willReturn('url/of/the/change.txt');

        $change = [
            'data' => [
                'hash'             => '98az7er654ert4s',
                'filePath'         => 'key/of/the/change.txt',
                'originalFilename' => 'the new change.txt',
            ]
        ];

        $this->present($value, $change)->shouldReturn(
            '<ul class="diff">' .
                '<li class="changed file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="url/of/the/change.txt">the new change.txt</a>' .
                '</li>' .
            '</ul>'
        );
    }

    function it_only_presents_old_file_if_a_new_one_is_not_provided(
        $generator,
        ProductValueInterface $value,
        FileInterface $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getKey()->willReturn('key/of/the/media.pdf');
        $media->getHash()->willReturn('54qsfda8e7r54f');
        $media->getOriginalFilename()->willReturn('original_foo.pdf');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => urlencode('key/of/the/media.pdf')])
            ->willReturn('url/of/the/media.pdf');

        $this->present($value, ['data' => []])->shouldReturn(
            '<ul class="diff">' .
                '<li class="base file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="url/of/the/media.pdf">original_foo.pdf</a>' .
                '</li>' .
            '</ul>'
        );
    }
}
