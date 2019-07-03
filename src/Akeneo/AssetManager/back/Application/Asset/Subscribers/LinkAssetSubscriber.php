<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Query\Asset\FindPropertyAccessibleAssetInterface;
use Akeneo\AssetManager\Infrastructure\Rule\RuleExecutor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAssetSubscriber implements EventSubscriberInterface
{
    /** @var RuleExecutor */
    private $ruleExecutor;

    /** @var FindPropertyAccessibleAssetInterface */
    private $findPropertyAccessibleAsset;

    public function __construct(
        RuleExecutor $ruleExecutor,
        FindPropertyAccessibleAssetInterface $findPropertyAccessibleAsset
    ) {
        $this->ruleExecutor = $ruleExecutor;
        $this->findPropertyAccessibleAsset = $findPropertyAccessibleAsset;
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
        $asset = $this->findPropertyAccessibleAsset->find($assetCreatedEvent->getAssetFamilyIdentifier(), $assetCreatedEvent->getAssetCode());

        $ruleTemplate = RuleTemplate::createFromNormalized([
            'conditions' => [
                [
                    'field' => 'sku',
                    'operator' => Operators::EQUALS,
                    'value' => '1111111304'
                ]
            ],
            'actions' => [[
                'type' => 'add',
                'field' => 'new_asset_multiple_link',
                'items' => ['{{code}}']
            ]]
        ]);

        $this->ruleExecutor->execute($ruleTemplate, $asset);
    }
}
