<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\RuleTemplateExecutor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAssetSubscriber implements EventSubscriberInterface
{
    /** @var RuleTemplateExecutor */
    private $ruleExecutor;

    public function __construct(RuleTemplateExecutor $ruleExecutor)
    {
        $this->ruleExecutor = $ruleExecutor;
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
        $this->ruleExecutor->execute($assetCreatedEvent->getAssetFamilyIdentifier(), $assetCreatedEvent->getAssetCode());
    }
}
