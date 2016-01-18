<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Prophecy\Argument;

class FileNormalizerSpec extends ObjectBehavior
{
    function let(FileExporterPathGeneratorInterface $pathGenerator)
    {
        $this->beConstructedWith($pathGenerator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Flat\FileNormalizer');
    }

    function it_normalizes_a_file_for_media_export($pathGenerator, FileInfoInterface $fileInfo, ProductValueInterface $value)
    {
        $pathGenerator->generate($value, Argument::any())->willReturn('path/to/export/file.txt');
        $fileInfo->getKey()->willReturn('key/of/file.txt');
        $fileInfo->getStorage()->willReturn(FileStorage::CATALOG_STORAGE_ALIAS);

        $this->normalize(
            $fileInfo,
            null,
            ['identifier' => null, 'value' => $value, 'prepare_copy' => true]
        )->shouldReturn([
            'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS,
            'filePath' => 'key/of/file.txt',
            'exportPath' => 'path/to/export/file.txt',
        ]);
    }

    function it_normalizes_a_file_for_versioning(FileInfoInterface $fileInfo)
    {
        $fileInfo->getKey()->willReturn('key/of/file.txt');

        $this->normalize(
            $fileInfo,
            null,
            ['versioning' => true, 'field_name' => 'picture']
        )->shouldReturn(['picture' => 'key/of/file.txt']);
    }

    function it_normalizes_a_file_for_product_export(
        $pathGenerator,
        ProductValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $pathGenerator->generate($value, Argument::any())->willReturn('path/to/export/file.txt');
        $fileInfo->getKey()->willReturn('key/of/file.txt');
        $fileInfo->getStorage()->willReturn(FileStorage::CATALOG_STORAGE_ALIAS);

        $this->normalize(
            $fileInfo,
            null,
            ['identifier' => null, 'value' => $value, 'field_name' => 'picture']
        )->shouldReturn(['picture' => 'path/to/export/file.txt']);
    }

    function it_supports_files_and_internal_api(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization($fileInfo, 'csv')->shouldReturn(true);
        $this->supportsNormalization($fileInfo, 'flat')->shouldReturn(true);
        $this->supportsNormalization($fileInfo, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'csv')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'flat')->shouldReturn(false);
    }
}
