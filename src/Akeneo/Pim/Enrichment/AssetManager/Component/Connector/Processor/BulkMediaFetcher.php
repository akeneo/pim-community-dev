<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor;

use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

class BulkMediaFetcher
{
    /** @var FileFetcherInterface */
    private $mediaFetcher;

    /** @var FileExporterPathGeneratorInterface */
    private $fileExporterPath;

    /** @var FilesystemProvider */
    private $filesystemProvider;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var string[] */
    private $attributeCodes = [];

    /** @var array */
    private $errors = [];

    public function __construct(
        FileFetcherInterface $mediaFetcher,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FilesystemProvider $filesystemProvider,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->mediaFetcher = $mediaFetcher;
        $this->fileExporterPath = $fileExporterPath;
        $this->filesystemProvider = $filesystemProvider;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Fetch the media of the items to the target
     *
     * @param ValueCollection $values
     * @param string $target
     * @param string $identifier
     */
    public function fetchAll(ValueCollection $values, $target, $identifier)
    {
        $this->errors = [];
        $target = DIRECTORY_SEPARATOR !== substr($target, -1) ? $target . DIRECTORY_SEPARATOR : $target;

        foreach ($values as $value) {
            $data = $value->getData();
            if ($data instanceof FileData) {
                $exportPath = $this->fileExporterPath->generate(
                    [
                        'locale' => $value->getLocaleReference()->normalize(),
                        'scope' => $value->getChannelReference()->normalize(),
                    ],
                    [
                        'identifier' => $identifier,
                        'code' => $this->getAttributeCode($value->getAttributeIdentifier()),
                    ]
                );

                $this->fetch(
                    [
                        'from' => $data->getKey(),
                        'to' => [
                            'filePath' => $target . $exportPath,
                            'filename' => $data->getOriginalFilename()
                        ],
                        'storage' => Storage::FILE_STORAGE_ALIAS,
                    ]
                );
            }
        }
    }

    /**
     * Get an array of errors
     *
     * @return array
     *  [
     *      [
     *          'message' => (string) 'The media has not been copied',
     *          'media'  => [
     *              'from'    => (string) 'a/b/c/d/my_picture.jpg',
     *              'to'      => [
     *                  'filePath' => (string) '/tmp/files/identifier/code/',
     *                  'filename' => (string) 'my picture.jpg'
     *              ],
     *              'storage' => (string) 'assetStorage',
     *          ]
     *      ],
     *      [...]
     *  ]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Fetch a media to the target
     *
     * @param array $media
     */
    private function fetch(array $media)
    {
        try {
            $filesystem = $this->filesystemProvider->getFilesystem($media['storage']);
            $this->mediaFetcher->fetch($filesystem, $media['from'], $media['to']);
        } catch (FileTransferException $e) {
            $this->addError(
                $media,
                'The media has not been found or is not currently available'
            );
        } catch (\LogicException $e) {
            $this->addError($media, sprintf('The media has not been copied. %s', $e->getMessage()));
        }
    }

    /**
     * @param array $media
     * @param string $message
     */
    private function addError(array $media, $message): void
    {
        $this->errors[] = [
            'message' => $message,
            'media' => $media,
        ];
    }

    private function getAttributeCode(AttributeIdentifier $attributeIdentifier): string
    {
        $identifier = $attributeIdentifier->__toString();
        if (!isset($this->attributeCodes[$identifier])) {
            $this->attributeCodes[$identifier] = $this->attributeRepository
                ->getByIdentifier($attributeIdentifier)->getCode()->__toString();
        }

        return $this->attributeCodes[$identifier];
    }
}
