<?php

namespace Akeneo\Component\FileStorage\File;

use Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Query\FindKeyByHashQuery;
use Akeneo\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\MountManager;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

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

    /** @var FindKeyByHashQuery */
    private $findKeyByHashQuery;

    // TODO on 3.1, remove the null default value
    public function __construct(
        MountManager $mountManager,
        SaverInterface $saver,
        FileInfoFactoryInterface $factory,
        ?FindKeyByHashQuery $findKeyByHashQuery = null
    ) {
        $this->mountManager = $mountManager;
        $this->saver = $saver;
        $this->factory = $factory;
        $this->findKeyByHashQuery = $findKeyByHashQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $localFile, $destFsAlias, $deleteRawFile = false)
    {
        $filesystem = $this->mountManager->getFilesystem($destFsAlias);
        $file = $this->factory->createFromRawFile($localFile, $destFsAlias);

        // TODO on 3.1, remove the null test
        $existingFileKey = null;
        if (null !== $this->findKeyByHashQuery) {
            $existingFileKey = $this->findKeyByHashQuery->fetchKey($file->getHash());
        }

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
