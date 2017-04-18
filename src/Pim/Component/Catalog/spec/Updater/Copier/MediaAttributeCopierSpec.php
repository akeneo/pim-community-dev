<?php

namespace spec\Pim\Component\Catalog\Updater\Copier;

use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MediaAttributeCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        FileFetcherInterface $fileFetcher,
        FileStorerInterface $fileStorer,
        FilesystemProvider $filesystemProvider
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $fileFetcher,
            $fileStorer,
            $filesystemProvider,
            ['media'],
            ['media']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier');
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Copier\CopierInterface');
    }

    function it_supports_media_attributes(
        AttributeInterface $fromMediaAttribute,
        AttributeInterface $toMediaAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromNumberAttribute,
        AttributeInterface $toNumberAttribute
    ) {
        $fromMediaAttribute->getType()->willReturn('media');
        $toMediaAttribute->getType()->willReturn('media');
        $this->supportsAttributes($fromMediaAttribute, $toMediaAttribute)->shouldReturn(true);

        $fromNumberAttribute->getType()->willReturn('pim_catalog_number');
        $toNumberAttribute->getType()->willReturn('pim_catalog_number');
        $this->supportsAttributes($fromNumberAttribute, $toNumberAttribute)->shouldReturn(false);

        $this->supportsAttributes($fromMediaAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supportsAttributes($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_when_a_product_value_has_the_values_and_the_media(
        $builder,
        $attrValidatorHelper,
        $filesystemProvider,
        FileInfoInterface $fromMedia,
        FileInfoInterface $toMedia,
        \SplFileInfo $rawFile,
        FileInfoInterface $fileInfo,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product,
        ProductValueInterface $fromProductValue,
        ProductValueInterface $toProductValue,
        FileStorerInterface $fileStorer,
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $fileSystem
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getData()->willReturn($fromMedia);
        $toProductValue->getData()->willReturn($toMedia);

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $fromMedia->getOriginalFilename()->willReturn('akeneo.jpg');
        $fromMedia->getKey()->willReturn('key');

        $filesystemProvider
            ->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)
            ->willReturn($fileSystem);

        $fileFetcher->fetch($fileSystem, 'key')->willReturn($rawFile);

        $fileStorer
            ->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS, false)
            ->willReturn($fileInfo);

        $fileInfo->setOriginalFilename('akeneo.jpg')->shouldBeCalled();

        $fileInfo->getKey()->willReturn('key');

        $builder->addOrReplaceProductValue($product, $toAttribute, $toLocale, $toScope, 'key');

        $this->copyAttributeData(
            $product,
            $product,
            $fromAttribute,
            $toAttribute,
            ['from_locale' => $fromLocale, 'to_locale' => $toLocale, 'from_scope' => $fromScope, 'to_scope' => $toScope]
        );
    }

    function it_copies_when_a_source_product_value_has_no_media(
        $builder,
        $attrValidatorHelper,
        $filesystemProvider,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product,
        ProductValueInterface $fromProductValue,
        ProductValueInterface $toProductValue,
        FileStorerInterface $fileStorer,
        FileFetcherInterface $fileFetcher,
        FileInfoInterface $fileInfo
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getData()->willReturn(null);

        $filesystemProvider->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)->shouldNotBeCalled();
        $fileFetcher->fetch(Argument::cetera())->shouldNotBeCalled();
        $fileStorer->store(Argument::cetera())->shouldNotBeCalled();
        $fileInfo->setOriginalFilename(Argument::any())->shouldNotBeCalled();

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $fileInfo->getKey()->shouldNotBeCalled();

        $builder->addOrReplaceProductValue($product, $toAttribute, $toLocale, $toScope, null);

        $this->copyAttributeData(
            $product,
            $product,
            $fromAttribute,
            $toAttribute,
            ['from_locale' => $fromLocale, 'to_locale' => $toLocale, 'from_scope' => $fromScope, 'to_scope' => $toScope]
        );
    }

    function it_copies_when_a_product_value_has_a_media_but_not_the_target_value(
        $builder,
        $attrValidatorHelper,
        $filesystemProvider,
        FileInfoInterface $fromMedia,
        FileInfoInterface $toMedia,
        \SplFileInfo $rawFile,
        FileInfoInterface $fileInfo,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product,
        ProductValueInterface $fromProductValue,
        FileStorerInterface $fileStorer,
        ProductValueInterface $toProductValue,
        FileFetcherInterface $fileFetcher,
        FilesystemInterface $fileSystem
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getData()->willReturn($fromMedia);

        $fromMedia->getOriginalFilename()->willReturn('akeneo.jpg');
        $fromMedia->getKey()->willReturn('key');

        $filesystemProvider
            ->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)
            ->willReturn($fileSystem);

        $fileFetcher->fetch($fileSystem, 'key')->willReturn($rawFile);

        $fileStorer
            ->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS, false)
            ->willReturn($fileInfo);

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $toProductValue->getData()->willReturn($toMedia);

        $builder->addOrReplaceProductValue($product, $toAttribute, $toLocale, $toScope, $fileInfo);

        $this->copyAttributeData(
            $product,
            $product,
            $fromAttribute,
            $toAttribute,
            ['from_locale' => $fromLocale, 'to_locale' => $toLocale, 'from_scope' => $fromScope, 'to_scope' => $toScope]
        );
    }
}
