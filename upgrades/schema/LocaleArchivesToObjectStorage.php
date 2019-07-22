<?php


namespace Pim\Upgrade\Schema;

use League\Flysystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Relocates import/export archives from a local filesystem to an object storage.
 *
 * The archives files are listed from the "%archive_dir%/import" and "%archive_dir%/export". Then, each existing and readable local file is
 * stored in the object storage.
 */
class LocaleArchivesToObjectStorage
{
    /** @var Finder */
    private $archiveFileSystem;

    /** @var Filesystem */
    private $objectStorage;

    /** @var string */
    private $archiveDirectory;

    /** @var array */
    private $errors = [];

    /** @var int */
    private $countRelocated = 0;

    public function __construct(
        Filesystem $filesystem,
        string $archiveDirectory
    ) {
        $this->objectStorage = $filesystem;
        $this->archiveFileSystem = $this->buildFinder($archiveDirectory);
        $this->archiveDirectory = $archiveDirectory;
    }

    public function countFiles(): int
    {
        return $this->archiveFileSystem->count();
    }

    public function countRelocated(): int
    {
        return $this->countRelocated;
    }

    public function relocateFiles(): array
    {
        foreach ($this->archiveFileSystem as $file) {
            try {
                $this->relocate($file);
            } catch (RelocateException $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        
        return $this->errors;
    }

    private function relocate(\SplFileInfo $fileInfo): void
    {
        $file = $this->open($fileInfo);

        $stored = $this->objectStorage->putStream($this->archiveKey($fileInfo), $file);
        if (!$stored) {
            if (is_resource($file)) {
                fclose($file);
            }

            throw new StoreException($this->archiveKey($fileInfo));
        }

        if (is_resource($file)) {
            fclose($file);
        }

        $this->countRelocated++;
    }

    /**
     * @return resource
     */
    private function open(\SplFileInfo $fileInfo)
    {
        $file = @fopen($fileInfo->getPathname(), 'r');
        if (false === $file) {
            throw new OpenFileException($fileInfo->getPathname());
        }

        return $file;
    }

    private function buildFinder(string $localStorageDirectory): Finder
    {
        $finder = new Finder();
        $dirs = [];
        if (is_dir($localStorageDirectory . DIRECTORY_SEPARATOR . 'import')) {
            $dirs[] = $localStorageDirectory . DIRECTORY_SEPARATOR . 'import';
        }
        if (is_dir($localStorageDirectory . DIRECTORY_SEPARATOR . 'export')) {
            $dirs[] = $localStorageDirectory . DIRECTORY_SEPARATOR . 'export';
        }
        
        $finder->files()->in($dirs);

        return $finder;
    }

    private function archiveKey(\SplFileInfo $fileInfo): string
    {
        return str_replace($this->archiveDirectory . DIRECTORY_SEPARATOR, '', $fileInfo->getPathname());
    }
}
