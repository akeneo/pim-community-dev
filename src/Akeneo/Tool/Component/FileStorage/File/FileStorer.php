<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileAlreadyExistsException;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\DuplicateObjectException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface
 * and save it to the database.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileStorer implements FileStorerInterface
{
    public function __construct(
        private FilesystemProvider $filesystemProvider,
        private SaverInterface $saver,
        private FileInfoFactoryInterface $factory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $localFile, string $destFsAlias, bool $deleteRawFile = false): FileInfoInterface
    {
        if (!is_file($localFile->getPathname())) {
            throw new InvalidFile(sprintf('The file "%s" does not exist.', $localFile->getPathname()));
        }

        $filesystem = $this->filesystemProvider->getFilesystem($destFsAlias);
        $file = $this->factory->createFromRawFile($localFile, $destFsAlias);

        $error = sprintf(
            'Unable to move the file "%s" to the "%s" filesystem.',
            $localFile->getPathname(),
            $destFsAlias
        );

        if (false === $resource = fopen($localFile->getPathname(), 'r')) {
            throw new FileTransferException($error);
        }

        try {
            $options = [];
            $mimeType = $file->getMimeType();
            if (null !== $mimeType) {
                /*
                 * AWS S3 (see PIM-5405) and Google Cloud Storage (see PIM-8673) require a Content-Type metadata to properly handle a file type.
                 * But each Flysystem adapter use is own Config format.
                 */
                $options['ContentType'] = $mimeType; // AWS S3
                $options['metadata']['contentType'] = $mimeType; // Google Cloud Storage
            }
            if ($filesystem->fileExists($file->getKey())) {
                throw UnableToWriteFile::atLocation($file->getKey(), 'The file already exists');
            }
            $filesystem->writeStream($file->getKey(), $resource, $options);
        } catch (FilesystemException $e) {
            throw new FileTransferException($error, $e->getCode(), $e);
        }

        try {
            $this->saver->save($file);
        } catch (DuplicateObjectException $e) {
            throw new FileAlreadyExistsException($e->getMessage());
        }

        if (true === $deleteRawFile) {
            $this->deleteRawFile($localFile);
        }

        return $file;
    }

    /**
     * @param \SplFileInfo $file
     *
     * @throws FileRemovalException
     */
    private function deleteRawFile(\SplFileInfo $file): void
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->remove($file->getPathname());
        } catch (IOException $e) {
            throw new FileRemovalException(
                sprintf('Unable to delete the file "%s".', $file->getPathname()),
                $e->getCode(),
                $e
            );
        }
    }
}
