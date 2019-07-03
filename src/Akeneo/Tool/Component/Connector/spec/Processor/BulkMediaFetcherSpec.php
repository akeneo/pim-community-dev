<?php

namespace spec\Akeneo\Tool\Component\Connector\Processor;

use Akeneo\Tool\Component\Connector\Processor\BulkMediaFetcher;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;

class BulkMediaFetcherSpec extends ObjectBehavior
{
    /** @var string */
    private $directory;

    function let(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FileExporterPathGeneratorInterface $fileExporterPath
    ) {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;

        $this->beConstructedWith($mediaFetcher, $filesystemProvider, $fileExporterPath);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BulkMediaFetcher::class);
    }

    function it_copies_media_to_the_export_dir(
        $mediaFetcher,
        $filesystemProvider,
        FileInfoInterface $fileInfo,
        WriteValueCollection $valuesCollection,
        \ArrayIterator $valuesIterator,
        MediaValueInterface $value,
        FilesystemInterface $filesystem
    ) {
        $fileInfo->getStorage()->willReturn('storageAlias');
        $fileInfo->getKey()->willReturn('a/b/c/d/product.jpg');
        $fileInfo->getOriginalFilename()->willReturn('my product.jpg');

        $value->getAttributeCode()->willReturn('my_picture');
        $value->getData()->willReturn($fileInfo);
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn(null);

        $valuesCollection->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, false);
        $valuesIterator->current()->willReturn($value);
        $valuesIterator->next()->shouldBeCalled();

        $filesystemProvider->getFilesystem('storageAlias')->willReturn($filesystem);

        $mediaFetcher->fetch($filesystem, 'a/b/c/d/product.jpg', [
            'filePath' => $this->directory,
            'filename' => 'my product.jpg'
        ])->shouldBeCalled();

        $this->fetchAll($valuesCollection, $this->directory, 'the_sku');
        $this->getErrors()->shouldHaveCount(0);
    }

    function it_allows_to_get_errors_if_the_copy_went_wrong(
        $mediaFetcher,
        $filesystemProvider,
        $fileExporterPath,
        FileInfoInterface $fileInfo,
        FileInfoInterface $fileInfo2,
        WriteValueCollection $valuesCollection,
        \ArrayIterator $valuesIterator,
        MediaValueInterface $value,
        MediaValueInterface $value2,
        FilesystemInterface $filesystem
    ) {
        $fileInfo->getStorage()->willReturn('storageAlias');
        $fileInfo->getKey()->willReturn('a/b/c/d/product.jpg');
        $fileInfo->getOriginalFilename()->willReturn('my product.jpg');

        $fileInfo2->getStorage()->willReturn('storageAlias');
        $fileInfo2->getKey()->willReturn('wrong-path.jpg');
        $fileInfo2->getOriginalFilename()->willReturn('my-second-media.jpg');

        $value->getAttributeCode()->willReturn('my_picture');
        $value->getData()->willReturn($fileInfo);
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn(null);

        $value2->getAttributeCode()->willReturn('my_picture');
        $value2->getData()->willReturn($fileInfo2);
        $value2->getLocaleCode()->willReturn('fr_FR');
        $value2->getScopeCode()->willReturn('ecommerce');

        $valuesCollection->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->current()->willReturn($value, $value2);
        $valuesIterator->next()->shouldBeCalled();

        $filesystemProvider->getFilesystem('storageAlias')->willReturn($filesystem);

        $mediaFetcher->fetch($filesystem, 'a/b/c/d/product.jpg', [
            'filePath' => $this->directory . 'files/the_sku/my_picture/en_US/',
            'filename' => 'my product.jpg'
        ])->willThrow(new FileTransferException());
        $fileExporterPath->generate(
            ['locale' => 'en_US', 'scope' => null],
            ['identifier' => 'the_sku', 'code' => 'my_picture']
        )->willReturn('files/the_sku/my_picture/en_US/');

        $mediaFetcher->fetch($filesystem, 'wrong-path.jpg', [
            'filePath' => $this->directory . 'files/the_sku/my_picture/fr_FR/ecommerce/',
            'filename' => 'my-second-media.jpg'
        ])->willThrow(new \LogicException('Something went wrong.'));
        $fileExporterPath->generate(
            ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ['identifier' => 'the_sku', 'code' => 'my_picture']
        )->willReturn('files/the_sku/my_picture/fr_FR/ecommerce/');

        $this->fetchAll($valuesCollection, $this->directory, 'the_sku');

        $this->getErrors()->shouldBeEqualTo([
            [
                'message' => 'The media has not been found or is not currently available',
                'media'   => [
                    'from'    => 'a/b/c/d/product.jpg',
                    'to'      => [
                        'filePath' => $this->directory . 'files/the_sku/my_picture/en_US/',
                        'filename' => 'my product.jpg',
                    ],
                    'storage' => 'storageAlias',
                ]
            ],
            [
                'message' => 'The media has not been copied. Something went wrong.',
                'media'  => [
                    'from'    => 'wrong-path.jpg',
                    'to'      => [
                        'filePath' => $this->directory . 'files/the_sku/my_picture/fr_FR/ecommerce/',
                        'filename' => 'my-second-media.jpg',
                    ],
                    'storage' => 'storageAlias',
                ]
            ]
        ]);
    }
}
