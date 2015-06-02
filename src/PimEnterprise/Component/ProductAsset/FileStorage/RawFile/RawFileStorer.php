<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use PimEnterprise\Component\ProductAsset\Exception\FileRemovalException;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;
use PimEnterprise\Component\ProductAsset\FileStorage\FileFactoryInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGeneratorInterface;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
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

        $resource = fopen($localFile->getPathname(), 'r');
        if (false === $filesystem->writeStream($file->getPathname(), $resource)) {
            throw new FileTransferException(
                sprintf('Unable to move the file "%s" to the "%s" filesystem.', $localFile->getPathname(), $destFsAlias)
            );
        }

        $this->saver->save($file);

        if (false === unlink($localFile->getPathname())) {
            throw new FileRemovalException(sprintf('Unable to delete the file "%s".', $localFile->getPathname()));
        }

        return $file;
    }
}
