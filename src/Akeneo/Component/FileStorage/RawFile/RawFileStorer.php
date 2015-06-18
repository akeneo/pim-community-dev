<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileStorage\RawFile;

use Akeneo\Component\FileStorage\FileFactoryInterface;
use Akeneo\Component\FileStorage\PathGeneratorInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\MountManager;
use PimEnterprise\Component\ProductAsset\Exception\FileRemovalException;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \Akeneo\Component\FileStorage\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class RawFileStorer implements RawFileStorerInterface
{
    /** @var SaverInterface */
    protected $saver;

    /** @var PathGeneratorInterface */
    protected $pathGenerator;

    /** @var MountManager */
    protected $mountManager;

    /** @var FileFactoryInterface */
    protected $factory;

    /**
     * @param PathGeneratorInterface $pathGenerator
     * @param MountManager           $mountManager
     * @param SaverInterface         $saver
     */
    public function __construct(
        PathGeneratorInterface $pathGenerator,
        MountManager $mountManager,
        SaverInterface $saver,
        FileFactoryInterface $factory
    ) {
        $this->pathGenerator = $pathGenerator;
        $this->mountManager  = $mountManager;
        $this->saver         = $saver;
        $this->factory       = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $localFile, $destFsAlias)
    {
        $filesystem  = $this->mountManager->getFilesystem($destFsAlias);
        $storageData = $this->pathGenerator->generate($localFile);
        $file        = $this->factory->create($localFile, $storageData, $destFsAlias);

        $error = sprintf(
            'Unable to move the file "%s" to the "%s" filesystem.',
            $localFile->getPathname(),
            $destFsAlias
        );

        if (false === $resource = fopen($localFile->getPathname(), 'r')) {
            throw new FileTransferException($error);
        }

        try {
            $isFileWritten = $filesystem->writeStream($file->getKey(), $resource);
        } catch (FileExistsException $e) {
            throw new FileTransferException($error, $e->getCode(), $e);
        }

        if (false === $isFileWritten) {
            throw new FileTransferException($error);
        }

        $this->saver->save($file, ['flush_only_object' => true]);
        $this->deleteRawFile($localFile);

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
