<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Application\Asset\Subscribers\IndexByAssetFamilyInBackgroundInterface;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Event\AttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\IndexAssetEventAggregator;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAssetEventAggregatorSpec extends ObjectBehavior
{
    function let(
        AssetIndexerInterface $assetIndexer,
        IndexByAssetFamilyInBackgroundInterface $indexByAssetFamilyInBackground
    ) {
        $this->beConstructedWith($assetIndexer, $indexByAssetFamilyInBackground);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexAssetEventAggregator::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [
                AssetUpdatedEvent::class     => 'whenAssetUpdated',
                AssetCreatedEvent::class     => 'whenAssetCreated',
                AttributeDeletedEvent::class => 'whenAttributeIsDeleted',
            ]
        );
    }

    function it_triggers_the_reindexation_of_an_updated_asset_when_flushed(AssetIndexerInterface $assetIndexer)
    {
        $assetIdentifier = AssetIdentifier::fromString('starck');
        $assetIndexer->indexByAssetIdentifiers([$assetIdentifier])->shouldBeCalled();
        $assetIndexer->refresh()->shouldBeCalled();

        $this->whenAssetUpdated(
            new AssetUpdatedEvent(
                $assetIdentifier,
                AssetCode::fromString('starck'),
                AssetFamilyIdentifier::fromString('designer'),
            )
        );
        $this->flushEvents();
    }

    function it_triggers_the_indexation_of_a_created_asset_when_flushed(AssetIndexerInterface $assetIndexer)
    {
        $assetIdentifier = AssetIdentifier::fromString('starck');
        $assetIndexer->indexByAssetIdentifiers([$assetIdentifier])->shouldBeCalled();
        $assetIndexer->refresh()->shouldBeCalled();

        $this->whenAssetCreated(
            new AssetCreatedEvent(
                $assetIdentifier,
                AssetCode::fromString('starck'),
                AssetFamilyIdentifier::fromString('designer')
            )
        );
        $this->flushEvents();
    }

    function it_runs_a_reindexing_command_when_an_attribute_is_removed(
        IndexByAssetFamilyInBackgroundInterface $indexByAssetFamilyInBackground
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $this->whenAttributeIsDeleted(
            new AttributeDeletedEvent(
                $assetFamilyIdentifier,
                AttributeIdentifier::fromString('name_designer_123')
            )
        );
        $indexByAssetFamilyInBackground->execute($assetFamilyIdentifier)->shouldBeCalled();
    }
}

