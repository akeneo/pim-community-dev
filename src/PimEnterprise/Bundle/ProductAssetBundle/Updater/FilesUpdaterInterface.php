<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Updater;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface FilesUpdaterInterface
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param RawFileStorerInterface   $rawFileStorer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RawFileStorerInterface $rawFileStorer
    );

    /**
     * @param AssetInterface $asset
     */
    public function updateAssetFiles(AssetInterface $asset);
}
