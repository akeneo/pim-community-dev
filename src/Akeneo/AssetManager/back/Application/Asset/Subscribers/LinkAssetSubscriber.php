<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\ProductLinkRuleLauncherInterface;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAssetSubscriber implements EventSubscriberInterface
{
    /** @var ProductLinkRuleLauncherInterface */
    private $productLinkRuleLauncher;

    public function __construct(ProductLinkRuleLauncherInterface $productLinkRuleLauncher)
    {
        $this->productLinkRuleLauncher = $productLinkRuleLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetCreatedEvent::class => 'whenAssetCreated',
        ];
    }

    public function whenAssetCreated(AssetCreatedEvent $assetCreatedEvent): void
    {
//        TODO: This part will be reworked in an upcoming PR
//        $this->productLinkRuleLauncher->launch(
//            $assetCreatedEvent->getAssetFamilyIdentifier(),
//            $assetCreatedEvent->getAssetCode()
//        );
    }
}
