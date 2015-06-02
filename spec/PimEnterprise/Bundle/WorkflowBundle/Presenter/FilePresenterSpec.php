<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
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
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_file');
        $value->getAttribute()->willReturn($attribute);

        $this->supports($value)->shouldBe(true);
    }

    function it_presents_files_in_a_list(
        $generator,
        Model\ProductValueInterface $value,
        Model\ProductMedia $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getFilename()->willReturn('uploaded_bar.pdf');
        $media->getOriginalFilename()->willReturn('bar.pdf');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'uploaded_bar.pdf'])
            ->willReturn('/media/uploaded_bar.pdf');
        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'uploaded_foo.pdf'])
            ->willReturn('/media/uploaded_foo.pdf');

        $change = [
            'value' => [
                'filename' => 'uploaded_foo.pdf',
                'originalFilename' => 'foo.pdf',
            ]
        ];

        $this->present($value, $change)->shouldReturn(
            '<ul class="diff">' .
                '<li class="base file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="/media/uploaded_bar.pdf">bar.pdf</a>' .
                '</li>' .
                '<li class="changed file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="/media/uploaded_foo.pdf">foo.pdf</a>' .
                '</li>' .
            '</ul>'
        );
    }

    function it_only_presents_new_file_if_value_does_not_have_a_media_yet(
        $generator,
        Model\ProductValueInterface $value
    ) {
        $value->getMedia()->willReturn(null);

        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'uploaded_foo.pdf'])
            ->willReturn('/media/uploaded_foo.pdf');

        $change = [
            'value' => [
                'filename' => 'uploaded_foo.pdf',
                'originalFilename' => 'foo.pdf',
            ]
        ];

        $this->present($value, $change)->shouldReturn(
            '<ul class="diff">' .
                '<li class="changed file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="/media/uploaded_foo.pdf">foo.pdf</a>' .
                '</li>' .
            '</ul>'
        );
    }

    function it_only_presents_old_file_if_a_new_one_is_not_provided(
        $generator,
        Model\ProductValueInterface $value,
        Model\ProductMedia $media
    ) {
        $value->getMedia()->willReturn($media);
        $media->getFilename()->willReturn('uploaded_bar.pdf');
        $media->getOriginalFilename()->willReturn('bar.pdf');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => 'uploaded_bar.pdf'])
            ->willReturn('/media/uploaded_bar.pdf');

        $this->present($value, ['value' => []])->shouldReturn(
            '<ul class="diff">' .
                '<li class="base file">' .
                    '<i class="icon-file"></i>' .
                    '<a target="_blank" class="no-hash" href="/media/uploaded_bar.pdf">bar.pdf</a>' .
                '</li>' .
            '</ul>'
        );
    }
}
