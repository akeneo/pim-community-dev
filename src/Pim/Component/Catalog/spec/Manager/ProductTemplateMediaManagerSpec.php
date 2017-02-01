<?php

namespace spec\Pim\Component\Catalog\Manager;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\FileStorage;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateMediaManagerSpec extends ObjectBehavior
{
    function let(
        FileStorerInterface $fileStorer,
        NormalizerInterface $normalizer,
        ProductValueFactory $productValueFactory
    ) {
        $this->beConstructedWith($fileStorer, $normalizer, $productValueFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Manager\ProductTemplateMediaManager');
    }

    function it_uses_the_media_manager_to_handle_the_media_of_product_templates(
        $fileStorer,
        $normalizer,
        $productValueFactory,
        ProductTemplateInterface $template,
        ProductValueInterface $imageValue,
        ProductValueInterface $fileValue,
        ProductValueInterface $newImageValue,
        ProductValueInterface $newFileValue,
        FileInfoInterface $imageMedia,
        FileInfoInterface $fileInfoMedia,
        FileInfoInterface $fileInfoMediaUploaded,
        AttributeInterface $imageAttribute,
        AttributeInterface $fileAttribute
    ) {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $uploadedFile = new UploadedFile($pathname, 'uploaded file.txt');

        $template->getValues()->willReturn([$imageValue, $fileValue]);

        $imageValue->getMedia()->willReturn($imageMedia);
        $imageMedia->isRemoved()->willReturn(true);

        $imageValue->getAttribute()->willReturn($imageAttribute);
        $imageValue->getScope()->willReturn(null);
        $imageValue->getLocale()->willReturn(null);

        $fileValue->getMedia()->willReturn($fileInfoMedia);
        $fileInfoMedia->isRemoved()->willReturn(false);
        $fileInfoMedia->getUploadedFile()->willReturn($uploadedFile);
        $fileStorer->store($uploadedFile, FileStorage::CATALOG_STORAGE_ALIAS, true)->willReturn($fileInfoMediaUploaded);
        $fileInfoMediaUploaded->getKey()->willReturn('file_info_key');

        $fileValue->getAttribute()->willReturn($fileAttribute);
        $fileValue->getScope()->willReturn(null);
        $fileValue->getLocale()->willReturn(null);

        $productValueFactory->create($imageAttribute, null, null, null)->willReturn($newImageValue);
        $productValueFactory->create($fileAttribute, null, null, 'file_info_key')->willReturn($newFileValue);

        $normalizer
            ->normalize([$newImageValue, $newFileValue], 'standard', ['entity' => 'product'])
            ->willReturn(['file_info_key']);
        $template->setValuesData(['file_info_key'])->shouldBeCalled();

        $this->handleProductTemplateMedia($template);
    }

    function it_updates_normalized_product_template_values_if_media_values_have_been_handled(
        $normalizer,
        $productValueFactory,
        ProductTemplateInterface $imageTemplate,
        ProductTemplateInterface $textTemplate,
        ProductValueInterface $imageValue,
        ProductValueInterface $textValue,
        FileInfoInterface $imageMedia,
        AttributeInterface $attribute,
        ProductValueInterface $newImageValue
    ) {
        $imageTemplate->getValues()->willReturn([$imageValue]);
        $imageValue->getMedia()->willReturn($imageMedia);
        $imageMedia->isRemoved()->willReturn(true);

        $imageValue->getAttribute()->willReturn($attribute);
        $imageValue->getScope()->willReturn(null);
        $imageValue->getLocale()->willReturn(null);

        $productValueFactory->create($attribute, null, null, null)->willReturn($newImageValue);
        $normalizer->normalize([$newImageValue], 'standard', ['entity' => 'product'])->willReturn(['foobar']);
        $imageTemplate->setValuesData(['foobar'])->shouldBeCalled();

        $textTemplate->getValues()->willReturn([$textValue]);
        $textTemplate->setValuesData(Argument::any())->shouldNotBeCalled();

        $this->handleProductTemplateMedia($imageTemplate);
        $this->handleProductTemplateMedia($textTemplate);
    }
}
