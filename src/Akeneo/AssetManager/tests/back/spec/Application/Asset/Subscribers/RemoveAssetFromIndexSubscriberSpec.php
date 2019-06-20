<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\Subscribers\RemoveAssetFromIndexSubscriber;
use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetFamilyAssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveAssetFromIndexSubscriberSpec extends ObjectBehavior
{
    function let(AssetIndexerInterface $assetIndexer)
    {
        $this->beConstructedWith($assetIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveAssetFromIndexSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            AssetDeletedEvent::class => 'whenAssetDeleted',
            AssetFamilyAssetsDeletedEvent::class => 'whenAllAssetsDeleted',
        ]);
    }

    function it_triggers_the_unindexation_of_an_deleted_asset(AssetIndexerInterface $assetIndexer)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetIdentifier = AssetIdentifier::fromString('stark_identifier');
        $assetCode = AssetCode::fromString('stark');
        $assetIndexer->removeAssetByAssetFamilyIdentifierAndCode('designer', 'stark')->shouldBeCalled();

        $this->whenAssetDeleted(new AssetDeletedEvent($assetIdentifier, $assetCode, $assetFamilyIdentifier));
    }

    function it_triggers_the_unindexation_of_all_entity_assets_when_they_are_deleted(
        AssetIndexerInterface $assetIndexer
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetIndexer->removeByAssetFamilyIdentifier('designer')->shouldBeCalled();

        $this->whenAllAssetsDeleted(new AssetFamilyAssetsDeletedEvent($assetFamilyIdentifier));
    }
}
