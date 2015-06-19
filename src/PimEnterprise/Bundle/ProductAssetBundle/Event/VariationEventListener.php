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
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class VariationEventListener
{
    /** @var string */
    protected $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function onVariationUploaded(GenericEvent $event)
    {
        $pathFinder = new PhpExecutableFinder();
        $cmd = sprintf(
            '%s %s/console pim:asset:generate-missing-variations',
            $pathFinder->find(),
            $this->rootDir
        );
        exec($cmd . ' &');

        return $event;
    }
}
