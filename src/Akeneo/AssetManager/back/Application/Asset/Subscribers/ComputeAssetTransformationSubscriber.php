<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationLauncherInterface;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ComputeAssetTransformationSubscriber implements EventSubscriberInterface
{
    /** @var ComputeTransformationLauncherInterface */
    private $computeTransformationLauncher;

    public function __construct(ComputeTransformationLauncherInterface $computeTransformationLauncher)
    {
        $this->computeTransformationLauncher = $computeTransformationLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetUpdatedEvent::class => 'whenAssetUpdated',
            AssetCreatedEvent::class => 'whenAssetCreated',
        ];
    }

    public function whenAssetUpdated(AssetUpdatedEvent $assetUpdatedEvent): void
    {
        $this->computeTransformationLauncher->launch([$assetUpdatedEvent->getAssetIdentifier()]);
    }

    public function whenAssetCreated(AssetCreatedEvent $assetCreatedEvent): void
    {
        $this->computeTransformationLauncher->launch([$assetCreatedEvent->getAssetIdentifier()]);
    }
}
