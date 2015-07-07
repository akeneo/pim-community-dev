<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateMediaManagerSpec extends ObjectBehavior
{
    function let(MediaManager $mediaManager, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($mediaManager, $normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager');
    }

    function it_uses_the_media_manager_to_handle_the_media_of_product_templates(
        $mediaManager,
        $normalizer,
        ProductTemplateInterface $template,
        ProductValueInterface $imageValue,
        ProductMediaInterface $imageMedia,
        File $imageFile,
        AttributeInterface $image,
        ProductValueInterface $fileValue,
        ProductMediaInterface $fileMedia
    ) {
        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $template->getValues()->willReturn([$imageValue, $fileValue]);
        $template->setValuesData([])->willReturn($template);

        $imageValue->getMedia()->willReturn($imageMedia);
        $imageMedia->getFile()->willReturn($imageFile);
        $imageValue->getAttribute()->willReturn($image);
        $imageValue->getLocale()->willReturn('en_US');
        $imageValue->getScope()->willReturn('mobile');
        $image->getCode()->willReturn('main_image');

        $fileValue->getMedia()->willReturn($fileMedia);

        $mediaManager->handle($imageMedia, Argument::containingString('-main_image-en_US-mobile-'))->shouldBeCalled();
        $mediaManager->handle($fileMedia, null)->shouldBeCalled();

        $this->handleProductTemplateMedia($template);
    }

    function it_updates_normalized_product_template_values_if_media_values_have_been_handled(
        $normalizer,
        ProductTemplateInterface $imageTemplate,
        ProductTemplateInterface $textTemplate,
        ProductValueInterface $imageValue,
        ProductValueInterface $textValue,
        ProductMediaInterface $imageMedia
    ) {
        $normalizer->normalize(Argument::cetera())->willReturn([]);

        $imageTemplate->getValues()->willReturn([$imageValue]);
        $imageValue->getMedia()->willReturn($imageMedia);
        $imageTemplate->setValuesData([])->shouldBeCalled();

        $textTemplate->getValues()->willReturn([$textValue]);
        $textTemplate->setValuesData([])->shouldNotBeCalled();

        $this->handleProductTemplateMedia($imageTemplate);
        $this->handleProductTemplateMedia($textTemplate);
    }

    function it_generates_the_media_filename_prefix(ProductValueInterface $fileValue, AttributeInterface $file)
    {
        $fileValue->getAttribute()->willReturn($file);
        $file->getCode()->willReturn('file');
        $fileValue->getLocale()->willReturn('de_DE');
        $fileValue->getScope()->willReturn('print');

        $prefix = $this->generateFilenamePrefix($fileValue);
        $prefix->shouldMatch('/-file-de_DE-print-/');
    }
}
