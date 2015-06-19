<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Updater;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface FilesUpdaterInterface
{
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RawFileStorerInterface $rawFileStorer
    );

    /**
     * @param AssetInterface $asset
     */
    public function updateAssetFiles(AssetInterface $asset);
}
