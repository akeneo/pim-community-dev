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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Processor;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class BulkAssetMediaFetcher
{
    private FileFetcherInterface $mediaFetcher;
    private FilesystemProvider $filesystemProvider;
    private FileExporterPathGeneratorInterface $fileExporterPath;
    private array $errors = [];

    public function __construct(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FileExporterPathGeneratorInterface $fileExporterPath
    ) {
        $this->mediaFetcher = $mediaFetcher;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileExporterPath = $fileExporterPath;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function fetchAllForAssetRawValuesAndReturnPaths(
        array $productValue,
        array $assetMainMediaValues,
        string $basePath,
        string $identifier,
        string $attributeCode
    ): array {
        $this->errors = [];
        $pathsForValue = [];

        foreach ($assetMainMediaValues as $value) {
            if (is_array($value['data'])) {
                $exportPath = $this->fileExporterPath->generate(
                    [
                        'locale' => $productValue['locale'],
                        'scope' => $productValue['scope'],
                    ],
                    [
                        'identifier' => $identifier,
                        'code' => $attributeCode,
                    ]
                );
                $this->fetch(
                    $value['data']['filePath'],
                    $basePath . $exportPath,
                    $value['data']['originalFilename']
                );
                $pathsForValue[] = sprintf("%s%s", $exportPath, $value['data']['originalFilename']);
            } else {
                // The path is the link
                $pathsForValue[] = $value['data'];
            }
        }

        return $pathsForValue;
    }

    /**
     * Fetch a media to the target
     */
    protected function fetch(string $fromPath, string $toPath, string $filename)
    {
        try {
            $filesystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
            $this->mediaFetcher->fetch($filesystem, $fromPath, [
                'filePath' => $toPath,
                'filename' => $filename,
            ]);
        } catch (FileTransferException $e) {
            $this->errors[] = [
                'reason' => sprintf('The media has not been found or is not currently available'),
                'item' => new DataInvalidItem([
                    'from' => $fromPath,
                    'to' => ['filePath' => $toPath, 'filename' => $filename],
                    'storage' => Storage::FILE_STORAGE_ALIAS,
                ]),
            ];
        } catch (\LogicException $e) {
            $this->errors[] = [
                'reason' => sprintf('The media has not been copied. %s', $e->getMessage()),
                'item' => new DataInvalidItem([
                    'from' => $fromPath,
                    'to' => ['filePath' => $toPath, 'filename' => $filename],
                    'storage' => Storage::FILE_STORAGE_ALIAS,
                ]),
            ];
        }
    }
}
