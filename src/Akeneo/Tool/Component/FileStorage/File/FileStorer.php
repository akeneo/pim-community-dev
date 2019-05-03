<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Tool\Component\FileStorage\Path;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use InvalidArgumentException;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemNotFoundException;
use League\Flysystem\MountManager;

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

    /**
     * @param MountManager             $mountManager
     * @param SaverInterface           $saver
     * @param FileInfoFactoryInterface $factory
     */
    public function __construct(
        MountManager $mountManager,
        SaverInterface $saver,
        FileInfoFactoryInterface $factory
    ) {
        $this->mountManager = $mountManager;
        $this->saver = $saver;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $localFile, $destFsAlias, $deleteRawFile = false)
    {
        $file = $this->factory->createFromRawFile($localFile, 'pefTmpStorage');

        $error = sprintf(
            'Unable to move the file "%s" to the "%s" filesystem.',
            $localFile->getPathname(),
            $destFsAlias
        );

        try {
            $isFileWritten = $this->mountManager->move(
                (string) new Path('pefTmpStorage', $localFile->getPathname()),
                (string) new Path($destFsAlias, $file->getKey())
            );
        } catch (InvalidArgumentException | FilesystemNotFoundException | FileExistsException $e) {
            throw new FileTransferException($error, $e->getCode(), $e);
        }

        if (false === $isFileWritten) {
            throw new FileTransferException($error);
        }

        $this->saver->save($file);

        return $file;
    }
}
