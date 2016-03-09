<?php

namespace Akeneo\Component\FileStorage\File;

use Akeneo\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\MountManager;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \Akeneo\Component\FileStorage\Model\FileInfoInterface
 * and save it to the database.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileStorer implements FileStorerInterface
{
    /** @var SaverInterface */
    protected $saver;

    /** @var MountManager */
    protected $mountManager;

    /** @var FileInfoFactoryInterface */
    protected $factory;

    /** @var FileInfoRepositoryInterface */
    protected $repository;

    /**
     * @param MountManager                $mountManager
     * @param SaverInterface              $saver
     * @param FileInfoFactoryInterface    $factory
     * @param FileInfoRepositoryInterface $repository
     */
    public function __construct(
        MountManager $mountManager,
        SaverInterface $saver,
        FileInfoFactoryInterface $factory,
        FileInfoRepositoryInterface $repository
    ) {
        $this->mountManager = $mountManager;
        $this->saver        = $saver;
        $this->factory      = $factory;
        $this->repository   = $repository;
    }

    /**
     * Find previously saved file copy. If copy not found, save new file.
     *
     * @param \SplFileInfo $rawFile       file to store
     * @param string       $destFsAlias   alias of the destination filesystem
     * @param bool         $deleteRawFile should the raw file be deleted once stored in the VFS or not ?
     *
     * @return FileInfoInterface
     */
    public function store(\SplFileInfo $localFile, $destFsAlias, $deleteRawFile = false)
    {
        $file = $this->findCopy($localFile);

        if (is_null($file)) {
            $file = $this->saveFile($localFile, $destFsAlias);
        }

        if (true === $deleteRawFile) {
            $this->deleteRawFile($localFile);
        }

        return $file;
    }

    /**
     * Calculate hash and finds file copy in database.
     *
     * @param \SplFileInfo $localFile
     *
     * @return FileInfoInterface|null
     */
    protected function findCopy(\SplFileInfo $localFile)
    {
        $hash = sha1_file($localFile->getPathname());

        return $this->repository->findOneByHash($hash);
    }

    /**
     * Move a file to the storage destination filesystem
     * transforms it as a \Akeneo\Component\FileStorage\Model\FileInfoInterface
     * and save it to the database.
     *
     * @param \SplFileInfo $file
     * @param string $destFsAlias
     *
     * @throws FileTransferException
     *
     * @return FileInfoInterface
     */
    protected function saveFile(\SplFileInfo $localFile, $destFsAlias)
    {
        $filesystem = $this->mountManager->getFilesystem($destFsAlias);
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
                $options['ContentType'] = $mimeType;
            }
            $isFileWritten = $filesystem->writeStream($file->getKey(), $resource, $options);
        } catch (FileExistsException $e) {
            throw new FileTransferException($error, $e->getCode(), $e);
        }

        if (false === $isFileWritten) {
            throw new FileTransferException($error);
        }

        $this->saver->save($file);

        return $file;
    }

    /**
     * @param \SplFileInfo $file
     *
     * @throws FileRemovalException
     */
    protected function deleteRawFile(\SplFileInfo $file)
    {
        $fs = new Filesystem();

        try {
            $fs->remove($file->getPathname());
        } catch (IOException $e) {
            throw new FileRemovalException(
                sprintf('Unable to delete the file "%s".', $file->getPathname()),
                $e->getCode(),
                $e
            );
        }
    }
}
