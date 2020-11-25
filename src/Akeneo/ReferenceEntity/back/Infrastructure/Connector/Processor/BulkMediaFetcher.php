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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Processor;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

class BulkMediaFetcher
{
    private const FILE_STORAGE_ALIAS = 'catalogStorage';

    private FileFetcherInterface $mediaFetcher;
    private FileExporterPathGeneratorInterface $fileExporterPath;
    private FilesystemProvider $filesystemProvider;
    private AttributeRepositoryInterface $attributeRepository;
    /** @var string[] */
    private array $attributeCodes = [];
    private array $errors = [];

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
     */
    public function fetchAll(ValueCollection $values, string $target, string $identifier): void
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
                            'filename' => $data->normalize()['originalFilename'],
                        ],
                        'storage' => self::FILE_STORAGE_ALIAS,
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
     *              'storage' => (string) 'catalogStorage',
     *          ]
     *      ],
     *      [...]
     *  ]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Fetch a media to the target
     *
     * @param array $media
     */
    private function fetch(array $media): void
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

    private function addError(array $media, string $message): void
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
