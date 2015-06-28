<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use Akeneo\Component\Console\CommandLauncher;

/**
 * Asset events listenener
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetEventListener
{
    /** @var CommandLauncher */
    protected $commandLauncher;

    /**
     * @param CommandLauncher $commandLauncher
     */
    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    /**
     * Generate missing variations for one asset or for all assets
     * Triggered by AssetEvent::FILES_UPLOAD_POST
     *
     * @param AssetEvent $event
     *
     * @return AssetEvent
     */
    public function onAssetFilesUploaded(AssetEvent $event)
    {
        $asset      = $event->getSubject();
        $cmd        = 'pimee:asset:generate-missing-variation-files';
        $background = true;

        if (null !== $asset) {
            $background = false;
            $cmd .= sprintf(' --asset=%s', $asset->getCode());
        }

        $this->commandLauncher->execute($cmd, $background);

        return $event;
    }
}
