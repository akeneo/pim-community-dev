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
    private $asynchronousProductLinkRuleLauncher;

    public function __construct(ProductLinkRuleLauncherInterface $asynchronousProductLinkRuleLauncher)
    {
        $this->asynchronousProductLinkRuleLauncher = $asynchronousProductLinkRuleLauncher;
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
        $this->asynchronousProductLinkRuleLauncher->launch(
            $assetCreatedEvent->getAssetFamilyIdentifier(),
            $assetCreatedEvent->getAssetCode()
        );
    }
}
