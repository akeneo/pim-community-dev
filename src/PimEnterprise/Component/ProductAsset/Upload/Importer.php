<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
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

    /**
     * @param UploadCheckerInterface $uploadChecker
     * @param FileStorerInterface    $fileStorer
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        FileStorerInterface $fileStorer
    ) {
        $this->uploadChecker = $uploadChecker;
        $this->fileStorer = $fileStorer;
    }

    /**
     * {@inheritdoc}
     *
     * - check uploaded files
     * - Move files from tmp uploaded storage to tmp imported storage
     */
    public function import(UploadContext $uploadContext)
    {
        $files           = [];
        $fileSystem      = new Filesystem();
        $uploadDirectory = $uploadContext->getTemporaryUploadDirectory();
        $importDirectory = $uploadContext->getTemporaryImportDirectory();

        $storedFiles = array_diff(scandir($uploadDirectory), ['.', '..']);

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
                $files[]         = $result;
            } else {
                $filePath = $uploadDirectory . DIRECTORY_SEPARATOR . $file;
                $newPath  = $importDirectory . DIRECTORY_SEPARATOR . $file;
                $fileSystem->rename($filePath, $newPath);
                $files[] = $result;
            }
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportedFiles(UploadContext $uploadContext)
    {
        $importDir    = $uploadContext->getTemporaryImportDirectory();
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
        $checkedIsLocalized   = null !== $checkedFilenameInfos->getLocaleCode();

        $filenamesIterator = new \ArrayIterator($otherFilenames);

        while ($filenamesIterator->valid()) {
            $filename = $filenamesIterator->current();

            $comparedInfos       = $this->uploadChecker->getParsedFilename($filename);
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
}
