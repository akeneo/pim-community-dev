<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Query\FindKeyByHashAndNameQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\MountManager;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface
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

    /** @var FindKeyByHashAndNameQueryInterface */
    private $findKeyByHashAndNameQuery;

    public function __construct(
        MountManager $mountManager,
        SaverInterface $saver,
        FileInfoFactoryInterface $factory,
        FindKeyByHashAndNameQueryInterface $findKeyByHashAndNameQuery
    ) {
        $this->mountManager = $mountManager;
        $this->saver = $saver;
        $this->factory = $factory;
        $this->findKeyByHashAndNameQuery = $findKeyByHashAndNameQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $localFile, $destFsAlias, $deleteRawFile = false): FileInfoInterface
    {
        $file = $this->factory->createFromRawFile($localFile, $destFsAlias);
        $existingFileKey = $this->findKeyByHashAndNameQuery->fetchKey($file->getHash(), $file->getOriginalFilename());

        if (null === $existingFileKey) {
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
                $filesystem = $this->mountManager->getFilesystem($destFsAlias);
                $isFileWritten = $filesystem->writeStream($file->getKey(), $resource, $options);
            } catch (FileExistsException $e) {
                throw new FileTransferException($error, $e->getCode(), $e);
            }

            if (false === $isFileWritten) {
                throw new FileTransferException($error);
            }
        } else {
            $file->setKey($existingFileKey);
        }

        $this->saver->save($file);

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
    protected function deleteRawFile(\SplFileInfo $file): void
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
