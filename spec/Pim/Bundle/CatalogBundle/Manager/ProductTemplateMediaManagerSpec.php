<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\FileStorage;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateMediaManagerSpec extends ObjectBehavior
{
    function let(RawFileStorerInterface $fileStorer, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($fileStorer, $normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager');
    }

    function it_uses_the_media_manager_to_handle_the_media_of_product_templates(
        $fileStorer,
        $normalizer,
        ProductTemplateInterface $template,
        ProductValueInterface $imageValue,
        FileInterface $imageMedia,
        ProductValueInterface $fileValue,
        FileInterface $fileMedia,
        FileInterface $fileMediaUploaded
    ) {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $uploadedFile = new UploadedFile($pathname, 'uploaded file.txt');

        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $template->getValues()->willReturn([$imageValue, $fileValue]);
        $template->setValuesData([])->willReturn($template);

        $imageValue->getMedia()->willReturn($imageMedia);
        $imageMedia->isRemoved()->willReturn(true);
        $imageValue->setMedia(null)->shouldBeCalled();

        $fileValue->getMedia()->willReturn($fileMedia);
        $fileMedia->isRemoved()->willReturn(false);
        $fileMedia->getUploadedFile()->willReturn($uploadedFile);
        $fileStorer->store($uploadedFile, FileStorage::CATALOG_STORAGE_ALIAS, true)->willReturn($fileMediaUploaded);
        $fileValue->setMedia($fileMediaUploaded)->shouldBeCalled();

        $this->handleProductTemplateMedia($template);
    }

    function it_updates_normalized_product_template_values_if_media_values_have_been_handled(
        $normalizer,
        ProductTemplateInterface $imageTemplate,
        ProductTemplateInterface $textTemplate,
        ProductValueInterface $imageValue,
        ProductValueInterface $textValue,
        FileInterface $imageMedia
    ) {
        $normalizer->normalize(Argument::cetera())->willReturn([]);

        $imageTemplate->getValues()->willReturn([$imageValue]);
        $imageValue->getMedia()->willReturn($imageMedia);
        $imageMedia->isRemoved()->willReturn(true);
        $imageValue->setMedia(null)->shouldBeCalled();
        $imageTemplate->setValuesData([])->shouldBeCalled();

        $textTemplate->getValues()->willReturn([$textValue]);
        $textTemplate->setValuesData([])->shouldNotBeCalled();

        $this->handleProductTemplateMedia($imageTemplate);
        $this->handleProductTemplateMedia($textTemplate);
    }
}
