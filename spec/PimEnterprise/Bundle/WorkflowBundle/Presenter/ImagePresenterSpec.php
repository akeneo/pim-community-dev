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
        $file2 = new \SplFileInfo(__FILE__);

        $value->getMedia()->willReturn($media);
        $media->getFilename()->willReturn(__FILE__);
        $media->getOriginalFilename()->willReturn('original_bar.jpg');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => __FILE__], true)
            ->willReturn(__FILE__);
        $generator
            ->generate('pim_enrich_media_show', ['filename' => __FILE__, 'filter' => 'thumbnail'])
            ->willReturn(__FILE__);
        $generator
            ->generate('pim_enrich_media_show', ['filename' => $file2->getFilename()], true)
            ->willReturn($file2->getPath());
        $generator
            ->generate('pim_enrich_media_show', ['filename' => $file2->getFilename(), 'filter' => 'thumbnail'])
            ->willReturn($file2->getPath());

        $change = [
            'value' => [
                'filename' => $file2->getFilename(),
                'filePath' => $file2->getPath(),
                'originalFilename' => 'change_bar.jpg',
            ]
        ];

        $this
            ->present($value, $change)
            ->shouldReturn(sprintf(
                '<ul class="diff">' .
                '<li class="base file"><img src="%s" title="original_bar.jpg" /></li>' .
                '<li class="changed file"><img src="%s" title="change_bar.jpg" /></li>' .
                '</ul>',
                __FILE__,
                $file2->getPath()
            ));
    }

    function it_presents_only_old_image_if_no_new_one_is_provided(
        $generator,
        Model\ProductValueInterface $value,
        Model\ProductMedia $media
    ) {
        $file = new \SplFileInfo(__FILE__);
        $value->getMedia()->willReturn($media);
        $media->getFilename()->willReturn($file->getFilename());
        $media->getOriginalFilename()->willReturn('bar.jpg');

        $generator
            ->generate('pim_enrich_media_show', ['filename' => $file->getFilename()], true)
            ->willReturn($file->getPath());
        $generator
            ->generate('pim_enrich_media_show', ['filename' => $file->getFilename(), 'filter' => 'thumbnail'])
            ->willReturn('/media/uploaded_bar.jpg');

        $this->present($value, ['value' => []])->shouldReturn(
            '<ul class="diff">' .
            '<li class="base file"><img src="/media/uploaded_bar.jpg" title="bar.jpg" /></li>' .
            '</ul>'
        );
    }

    function it_presents_only_new_image_if_there_is_no_old_one(
        $generator,
        Model\ProductValueInterface $value
    ) {
        $file = new \SplFileInfo(__FILE__);
        $value->getMedia()->willReturn(null);

        $generator
            ->generate('pim_enrich_media_show', ['filename' => $file->getFilename(), 'filter' => 'thumbnail'])
            ->willReturn('/media/bar.jpg');

        $change = [
            'value' => [
                'filename' => $file->getFilename(),
                'filePath' => $file->getPath(),
                'originalFilename' => 'original_bar.jpg',
            ]
        ];

        $this
            ->present($value, $change)
            ->shouldReturn(
                '<ul class="diff">' .
                    '<li class="changed file"><img src="/media/bar.jpg" title="original_bar.jpg" /></li>' .
                '</ul>'
            );
    }
}
