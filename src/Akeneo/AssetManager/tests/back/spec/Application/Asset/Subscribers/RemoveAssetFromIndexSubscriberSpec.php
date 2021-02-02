<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\Subscribers\RemoveAssetFromIndexSubscriber;
use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetsDeletedEvent;
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
            AssetsDeletedEvent::class => 'whenMultipleAssetsDeleted',
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

    function it_triggers_the_unindexation_of_multiple_assets_when_they_are_deleted(
        AssetIndexerInterface $assetIndexer
    ) {
        $assetCodes = [AssetCode::fromString('packshot_1'), AssetCode::fromString('packshot_2')];
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetIndexer->removeAssetByAssetFamilyIdentifierAndCodes('designer', ['packshot_1', 'packshot_2'])->shouldBeCalled();

        $this->whenMultipleAssetsDeleted(new AssetsDeletedEvent($assetFamilyIdentifier, $assetCodes));
    }
}
