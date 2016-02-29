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
use Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository;

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

    /** @var  FileInfoRepository */
    protected $repository;

    /**
     * @param MountManager             $mountManager
     * @param SaverInterface           $saver
     * @param FileInfoFactoryInterface $factory
     * @param FileInfoRepository       $repository
     */
    public function __construct(
        MountManager $mountManager,
        SaverInterface $saver,
        FileInfoFactoryInterface $factory,
        FileInfoRepository $repository
    ) {
        $this->mountManager = $mountManager;
        $this->saver        = $saver;
        $this->factory      = $factory;
        $this->repository   = $repository;
    }

    /**
     * {@inheritdoc}
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
     * @param \SplFileInfo $file
     */
    public function findCopy(\SplFileInfo $localFile)
    {
        $hash = sha1_file($localFile->getPathname());
        return $this->repository->findOneByHash($hash);
    }

    /**
     * @param \SplFileInfo $file
     * @param string $destFsAlias
     */
    public function saveFile(\SplFileInfo $localFile, $destFsAlias)
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
