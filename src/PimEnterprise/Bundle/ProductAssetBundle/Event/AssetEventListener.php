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

use PimEnterprise\Bundle\ProductAssetBundle\JobLauncher\CommandLauncher;

/**
 * Asset events listenener
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetEventListener
{
    /** @var CommandLauncher */
    protected $launcher;

    /**
     * @param CommandLauncher $launcher
     */
    public function __construct(CommandLauncher $launcher)
    {
        $this->launcher = $launcher;
    }

    /**
     * Generate missing variations for one asset or for all assets
     *
     * Trigerred by AssetEvent::FILES_UPLOAD_POST
     *
     * @param AssetEvent $event
     *
     * @return AssetEvent
     */
    public function onAssetFilesUploaded(AssetEvent $event)
    {
        $cmd = 'pim:asset:generate-missing-variation-files';

        $asset = $event->getSubject();

        if (null !== $asset) {
            $cmd .= sprintf(' --asset=%s', $asset->getCode());
        }

        $this->launcher->launchCommand($cmd);

        return $event;
    }
}
