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

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Asset events listenener
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetEventListener
{
    /** @var string */
    protected $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
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
    public function onVariationUploaded(AssetEvent $event)
    {
        $pathFinder = new PhpExecutableFinder();
        $cmd = sprintf(
            '%s %s/console pim:asset:generate-missing-variations',
            $pathFinder->find(),
            $this->rootDir
        );

        $asset = $event->getSubject();

        if (null !== $asset) {
            $cmd .= sprintf(' --asset=%s', $asset->getCode());
        }

        exec($cmd . ' &');

        return $event;
    }
}
