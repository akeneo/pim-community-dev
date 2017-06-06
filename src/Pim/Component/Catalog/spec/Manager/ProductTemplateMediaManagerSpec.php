<?php

namespace spec\Pim\Component\Catalog\Manager;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\ProductValue\MediaProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductTemplateMediaManagerSpec extends ObjectBehavior
{
    function let(
        FileStorerInterface $fileStorer,
        ProductValueFactory $productValueFactory
    ) {
        $this->beConstructedWith($fileStorer, $productValueFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Manager\ProductTemplateMediaManager');
    }

    function it_uses_the_media_manager_to_handle_the_media_of_product_templates(
        $fileStorer,
        $productValueFactory,
        ProductTemplateInterface $template,
        MediaProductValueInterface $imageValue,
        MediaProductValueInterface $fileValue,
        MediaProductValueInterface $newImageValue,
        MediaProductValueInterface $newFileValue,
        FileInfoInterface $imageMedia,
        FileInfoInterface $fileInfoMedia,
        FileInfoInterface $fileInfoMediaUploaded,
        AttributeInterface $imageAttribute,
        AttributeInterface $fileAttribute,
        ProductValueCollectionInterface $values,
        \ArrayIterator $valuesIterator
    ) {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $uploadedFile = new UploadedFile($pathname, 'uploaded file.txt');

        $template->getValues()->willReturn($values);

        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->current()->willReturn($imageValue, $fileValue);
        $valuesIterator->next()->shouldBeCalled();

        $imageValue->getData()->willReturn($imageMedia);
        $imageMedia->isRemoved()->willReturn(true);

        $imageValue->getAttribute()->willReturn($imageAttribute);
        $imageValue->getScope()->willReturn(null);
        $imageValue->getLocale()->willReturn(null);

        $fileValue->getData()->willReturn($fileInfoMedia);
        $fileInfoMedia->isRemoved()->willReturn(false);
        $fileInfoMedia->getUploadedFile()->willReturn($uploadedFile);
        $fileStorer->store($uploadedFile, FileStorage::CATALOG_STORAGE_ALIAS, true)->willReturn($fileInfoMediaUploaded);
        $fileInfoMediaUploaded->getKey()->willReturn('file_info_key');

        $fileValue->getAttribute()->willReturn($fileAttribute);
        $fileValue->getScope()->willReturn(null);
        $fileValue->getLocale()->willReturn(null);

        $productValueFactory->create($imageAttribute, null, null, null)->willReturn($newImageValue);
        $productValueFactory->create($fileAttribute, null, null, 'file_info_key')->willReturn($newFileValue);

        $values->remove($imageValue)->willReturn($values);
        $values->add($newImageValue)->willReturn($values);

        $values->remove($fileValue)->willReturn($values);
        $values->add($newFileValue)->willReturn($values);

        $template->setValues($values)->shouldBeCalled();

        $this->handleProductTemplateMedia($template);
    }

    function it_updates_normalized_product_template_values_if_media_values_have_been_handled(
        $productValueFactory,
        ProductTemplateInterface $imageTemplate,
        ProductTemplateInterface $textTemplate,
        MediaProductValueInterface $imageValue,
        ProductValueInterface $textValue,
        FileInfoInterface $imageMedia,
        AttributeInterface $attribute,
        MediaProductValueInterface $newImageValue,
        ProductValueCollectionInterface $imageValues,
        ProductValueCollectionInterface $textValues,
        \ArrayIterator $imageValuesIterator,
        \ArrayIterator $textValuesIterator
    ) {
        $imageTemplate->getValues()->willReturn($imageValues);

        $imageValues->getIterator()->willReturn($imageValuesIterator);
        $imageValuesIterator->rewind()->shouldBeCalled();
        $imageValuesIterator->valid()->willReturn(true, true, false);
        $imageValuesIterator->current()->willReturn($imageValue);
        $imageValuesIterator->next()->shouldBeCalled();

        $imageValue->getData()->willReturn($imageMedia);
        $imageMedia->isRemoved()->willReturn(true);

        $imageValue->getAttribute()->willReturn($attribute);
        $imageValue->getScope()->willReturn(null);
        $imageValue->getLocale()->willReturn(null);

        $productValueFactory->create($attribute, null, null, null)->willReturn($newImageValue);

        $imageValues->remove($imageValue)->willReturn($imageValues);
        $imageValues->add($newImageValue)->willReturn($imageValues);

        $imageTemplate->setValues($imageValues)->shouldBeCalled();

        $textTemplate->getValues()->willReturn($textValues);

        $textValues->getIterator()->willReturn($textValuesIterator);
        $textValuesIterator->rewind()->shouldBeCalled();
        $textValuesIterator->valid()->willReturn(true, true, false);
        $textValuesIterator->current()->willReturn($textValue);
        $textValuesIterator->next()->shouldBeCalled();

        $textTemplate->setValues(Argument::any())->shouldNotBeCalled();

        $this->handleProductTemplateMedia($imageTemplate);
        $this->handleProductTemplateMedia($textTemplate);
    }
}
