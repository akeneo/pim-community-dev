<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInterface;
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

    function it_normalizes_a_file_for_media_export($pathGenerator, FileInterface $file, ProductValueInterface $value)
    {
        $pathGenerator->generate($value, Argument::any())->willReturn('path/to/export/file.txt');
        $file->getKey()->willReturn('key/of/file.txt');
        $file->getStorage()->willReturn(FileStorage::CATALOG_STORAGE_ALIAS);

        $this->normalize(
            $file,
            null,
            ['identifier' => null, 'value' => $value, 'prepare_copy' => true]
        )->shouldReturn([
            'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS,
            'filePath' => 'key/of/file.txt',
            'exportPath' => 'path/to/export/file.txt',
        ]);
    }

    function it_normalizes_a_file_for_versioning(FileInterface $file)
    {
        $file->getKey()->willReturn('key/of/file.txt');

        $this->normalize(
            $file,
            null,
            ['versioning' => true, 'field_name' => 'picture']
        )->shouldReturn(['picture' => 'key/of/file.txt']);
    }

    function it_normalizes_a_file_for_product_export($pathGenerator, ProductValueInterface $value, FileInterface $file)
    {
        $pathGenerator->generate($value, Argument::any())->willReturn('path/to/export/file.txt');
        $file->getKey()->willReturn('key/of/file.txt');
        $file->getStorage()->willReturn(FileStorage::CATALOG_STORAGE_ALIAS);

        $this->normalize(
            $file,
            null,
            ['identifier' => null, 'value' => $value, 'field_name' => 'picture']
        )->shouldReturn(['picture' => 'path/to/export/file.txt']);
    }

    function it_supports_files_and_internal_api(FileInterface $file)
    {
        $this->supportsNormalization($file, 'csv')->shouldReturn(true);
        $this->supportsNormalization($file, 'flat')->shouldReturn(true);
        $this->supportsNormalization($file, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'csv')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'flat')->shouldReturn(false);
    }
}
