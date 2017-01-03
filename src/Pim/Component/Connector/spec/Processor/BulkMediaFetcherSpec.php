<?php

namespace spec\Pim\Component\Connector\Processor;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;

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

        $this->beConstructedWith($mediaFetcher, $filesystemProvider, $fileExporterPath, [
            'pim_catalog_file', 'pim_catalog_image'
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Processor\BulkMediaFetcher');
    }

    function it_copies_media_to_the_export_dir(
        $mediaFetcher,
        $filesystemProvider,
        FileInfoInterface $fileInfo,
        ArrayCollection $productValuesCollection,
        \ArrayIterator $valuesIterator,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        FilesystemInterface $filesystem
    ) {
        $fileInfo->getStorage()->willReturn('storageAlias');
        $fileInfo->getKey()->willReturn('a/b/c/d/product.jpg');
        $fileInfo->getOriginalFilename()->willReturn('my product.jpg');

        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getMedia()->willReturn($fileInfo);
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn(null);
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $attribute->getCode()->willReturn('my_picture');

        $productValuesCollection->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesCount = 1;
        $valuesIterator->valid()->will(
            function () use (&$valuesCount) {
                return $valuesCount-- > 0;
            }
        );
        $valuesIterator->next()->shouldBeCalled();
        $valuesIterator->current()->will(new ReturnPromise([$productValue]));

        $filesystemProvider->getFilesystem('storageAlias')->willReturn($filesystem);

        $mediaFetcher->fetch($filesystem, 'a/b/c/d/product.jpg', [
            'filePath' => $this->directory,
            'filename' => 'my product.jpg'
        ])->shouldBeCalled();

        $this->fetchAll($productValuesCollection, $this->directory, 'the_sku');
        $this->getErrors()->shouldHaveCount(0);
    }

    function it_allows_to_get_errors_if_the_copy_went_wrong(
        $mediaFetcher,
        $filesystemProvider,
        $fileExporterPath,
        FileInfoInterface $fileInfo,
        FileInfoInterface $fileInfo2,
        ArrayCollection $productValuesCollection,
        \ArrayIterator $valuesIterator,
        ProductValueInterface $productValue,
        ProductValueInterface $productValue2,
        AttributeInterface $attribute,
        FilesystemInterface $filesystem
    ) {
        $fileInfo->getStorage()->willReturn('storageAlias');
        $fileInfo->getKey()->willReturn('a/b/c/d/product.jpg');
        $fileInfo->getOriginalFilename()->willReturn('my product.jpg');

        $fileInfo2->getStorage()->willReturn('storageAlias');
        $fileInfo2->getKey()->willReturn('wrong-path.jpg');
        $fileInfo2->getOriginalFilename()->willReturn('my-second-media.jpg');

        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getMedia()->willReturn($fileInfo);
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn(null);

        $productValue2->getAttribute()->willReturn($attribute);
        $productValue2->getMedia()->willReturn($fileInfo2);
        $productValue2->getLocale()->willReturn('fr_FR');
        $productValue2->getScope()->willReturn('ecommerce');

        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $attribute->getCode()->willReturn('my_picture');

        $productValuesCollection->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesCount = 2;
        $valuesIterator->valid()->will(
            function () use (&$valuesCount) {
                return $valuesCount-- > 0;
            }
        );
        $valuesIterator->next()->shouldBeCalled();
        $valuesIterator->current()->will(new ReturnPromise([$productValue, $productValue2]));

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

        $this->fetchAll($productValuesCollection, $this->directory, 'the_sku');

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
