<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Akeneo\Component\FileMetadata\FileMetadataReaderFactoryInterface;
use Akeneo\Component\FileTransformer\FileTransformerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use PimEnterprise\Component\ProductAsset\FileStorage\FileHandler\FileHandlerInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGenerator;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class TmpAbstractAssetCommand extends ContainerAwareCommand
{
    /** @var MountManager */
    protected $mountManager;

    /** @var FileMetadataReaderFactoryInterface */
    protected $fileMetadataReaderFactory;

    /** @var FileTransformerInterface */
    protected $fileTransformer;

    /** @var PathGeneratorInterface */
    protected $pathGenerator;

    /** @var FileHandlerInterface */
    protected $localFileHandler;

    /**
     * @return SaverInterface
     */
    protected function getFileSaver()
    {
        return $this->getContainer()->get('pimee_product_asset.saver.file');
    }

    protected function getPathGenerator()
    {
        if (null === $this->pathGenerator) {
            $this->pathGenerator = new PathGenerator();
        }

        return $this->pathGenerator;
    }

    /**
     * @return MountManager
     */
    protected function getMountManager()
    {
        return $this->getContainer()->get('oneup_flysystem.mount_manager');
    }

    /**
     * @return FileMetadataReaderFactoryInterface
     */
    protected function getFileMetadataReaderFactory()
    {
        return $this->getContainer()->get('akeneo_file_metadata.reader_factory');
    }

    /**
     * @return FileTransformerInterface
     */
    protected function getFileTransformer()
    {
        return $this->getContainer()->get('akeneo_transformer.file_transformer');
    }
}
