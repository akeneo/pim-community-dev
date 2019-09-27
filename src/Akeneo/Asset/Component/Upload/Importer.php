<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Upload;

use Akeneo\Tool\Component\FileStorage\File\FileFetcher;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Import previously uploaded files
 * - read uploaded files
 * - move them to import directory where they will be collected by the processor
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class Importer implements ImporterInterface
{
    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var FilesystemProvider */
    private $filesystemProvider;

    /** @var FileFetcher */
    private $fileFetcher;

    /**
     * @param UploadCheckerInterface  $uploadChecker
     * @param FileStorerInterface     $fileStorer
     * @param FileFetcher|null        $fileFetcher
     * @param FilesystemProvider|null $filesystemProvider
     *
     * TODO @pullup in master: Make $fileFetcher and $filesystemProvider mandatory, and remove $fileStorer (unused)
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        FileStorerInterface $fileStorer,
        FileFetcher $fileFetcher = null,
        FilesystemProvider $filesystemProvider = null
    ) {
        $this->uploadChecker = $uploadChecker;
        $this->fileStorer = $fileStorer;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
    }

    /**
     * {@inheritdoc}
     *
     * - check uploaded files
     * - Move files from tmp uploaded storage to tmp imported storage
     */
    public function import(UploadContext $uploadContext, array $fileNames = [])
    {
        // TODO @pullup in master: remove this check, copy/paste content of "importFromUploadFilesystem" and remove function "importFromLocal"
        if (null === $this->fileFetcher || null === $this->filesystemProvider) {
            return $this->importFromLocal($uploadContext);
        } else {
            return $this->importFromUploadFilesystem($uploadContext, $fileNames);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImportedFiles(UploadContext $uploadContext)
    {
        $importDir = $uploadContext->getTemporaryImportDirectory();
        $importedFiles = [];
        if (is_dir($importDir)) {
            $importedFiles = array_diff(scandir($importDir), ['.', '..']);
            $importedFiles = array_map(function ($filename) use ($importDir) {
                return new \SplFileInfo($importDir . DIRECTORY_SEPARATOR . $filename);
            }, $importedFiles);
        }

        return $importedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportedFilesFromNames(UploadContext $uploadContext, array $fileNames)
    {
        $importDir = $uploadContext->getTemporaryImportDirectory();
        $importedFiles = [];
        if (is_dir($importDir)) {
            $importedFiles = array_map(function ($fileName) use ($importDir) {
                return new \SplFileInfo($importDir . DIRECTORY_SEPARATOR . $fileName);
            }, $fileNames);
        }

        return $importedFiles;
    }

    /**
     * Check for valid filename :
     * - code must be unique if not localized
     * - if twos file exist with the same code, one localized, one not, then the two are invalid
     *
     * @param string[] $storedFiles
     * @param string   $filenameToCheck
     *
     * @return bool
     */
    protected function isValidImportedFilename(array $storedFiles, $filenameToCheck)
    {
        $otherFilenames = array_diff($storedFiles, [$filenameToCheck]);

        $checkedFilenameInfos = $this->uploadChecker->getParsedFilename($filenameToCheck);
        $checkedIsLocalized = null !== $checkedFilenameInfos->getLocaleCode();

        $filenamesIterator = new \ArrayIterator($otherFilenames);

        while ($filenamesIterator->valid()) {
            $filename = $filenamesIterator->current();

            $comparedInfos = $this->uploadChecker->getParsedFilename($filename);
            $comparedIsLocalized = null !== $comparedInfos->getLocaleCode();

            if ($checkedFilenameInfos->getAssetCode() === $comparedInfos->getAssetCode() &&
                $checkedIsLocalized !== $comparedIsLocalized
            ) {
                return false;
            }
            $filenamesIterator->next();
        }

        return true;
    }

    private function importFromUploadFilesystem(UploadContext $uploadContext, array $fileNames = []): array
    {
        $files = [];
        $uploadFileSystem = $this->filesystemProvider->getFilesystem('tmpAssetUpload');
        $importDirectory = $uploadContext->getTemporaryImportDirectoryRelativePath();

        $filesToImport = array_filter($uploadFileSystem->listContents($importDirectory), function ($file) use ($fileNames) {
            return $file['type'] === 'file' && (empty($fileNames) || in_array($file['basename'], $fileNames));
        });

        $importedFileNames = array_map(function ($file) {
            return $file['basename'];
        }, $filesToImport);

        foreach ($filesToImport as $file) {
            $result = [
                'file'  => $file['basename'],
                'error' => null,
            ];
            if (!$this->isValidImportedFilename($importedFileNames, $file['basename'])) {
                $result['error'] = UploadMessages::ERROR_CONFLICTS;
            } else {
                $this->fileFetcher->fetch($uploadFileSystem, $file['path']);
            }

            if ($uploadFileSystem->has($file['path'])) {
                $uploadFileSystem->delete($file['path']);
            }

            $files[] = $result;
        }

        return $files;
    }

    // TODO: To remove on master. It exists only to avoid a BC-break
    private function importFromLocal(UploadContext $uploadContext): array
    {
        $files = [];
        $fileSystem = new Filesystem();
        $uploadDirectory = $uploadContext->getTemporaryUploadDirectory();
        $importDirectory = $uploadContext->getTemporaryImportDirectory();

        $storedFiles = array_map(function ($path) use ($uploadDirectory) {
            return $uploadDirectory . DIRECTORY_SEPARATOR . $path;
        }, array_diff(scandir($uploadDirectory), ['.', '..']));

        if (!is_dir($importDirectory)) {
            $fileSystem->mkdir($importDirectory);
        }

        foreach ($storedFiles as $file) {
            $result = [
                'file'  => $file,
                'error' => null,
            ];
            if (!$this->isValidImportedFilename($storedFiles, $file)) {
                $result['error'] = UploadMessages::ERROR_CONFLICTS;
                $files[] = $result;
            } else {
                $newPath = $importDirectory . DIRECTORY_SEPARATOR . basename($file);

                $fileSystem->rename($file, $newPath);
                $files[] = $result;
            }
        }

        return $files;
    }
}
