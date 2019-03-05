<?php

declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Event;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\Variation;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
use Webmozart\Assert\Assert;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
class MissingVariationsEventSubscriberSpec extends ObjectBehavior
{
    function let(
        VariationsCollectionFilesGeneratorInterface $generator,
        AssetFinderInterface $finder
    ) {
        $this->beConstructedWith($generator, $finder);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_post_upload_files_event()
    {
        $this::getSubscribedEvents()->shouldReturn([AssetEvent::POST_UPLOAD_FILES => 'onAssetFilesUploaded']);
    }

    function it_doesnt_generate_variations_on_an_asset_without_missing_varations($generator, $finder)
    {
        $asset = new Asset();
        $event = new AssetEvent($asset);

        $finder->retrieveVariationsNotGenerated($asset)->willReturn([]);

        $generator->generate([], true)->willReturn(new ProcessedItemList());

        $this->onAssetFilesUploaded($event)->shouldReturn($event);

        Assert::eq($event->getProcessedList(), new ProcessedItemList());
    }

    function it_generates_missing_variations_of_one_asset($generator, $finder)
    {
        $asset = new Asset();
        $variations = [new Variation(), new Variation()];

        $finder->retrieveVariationsNotGenerated($asset)->willReturn($variations);

        $processed = new ProcessedItemList();
        $processed->addItem($variations[0], '');
        $processed->addItem($variations[1], '');

        $generator->generate($variations, true)->willReturn($processed);

        $event = new AssetEvent($asset);
        $this->onAssetFilesUploaded($event)->shouldReturn($event);

        Assert::count($event->getProcessedList(), 2);
    }

    function it_generates_missing_variations_of_multiple_assets($generator, $finder)
    {
        $asset1 = new Asset();
        $asset1Variations = [
            new Variation(),
            (new Variation())->setLocked(true),
            new Variation()
        ];

        $asset2 = new Asset();
        $asset2Variations = [new Variation()];

        $finder->retrieveVariationsNotGenerated($asset1)->willReturn($asset1Variations);
        $finder->retrieveVariationsNotGenerated($asset2)->willReturn($asset2Variations);

        $asset1ProcessedVariations = new ProcessedItemList();
        $asset1ProcessedVariations->addItem($asset1Variations[0], '');
        $asset1ProcessedVariations->addItem($asset1Variations[2], '');

        $asset2ProcessedVariations = new ProcessedItemList();
        $asset2ProcessedVariations->addItem($asset2Variations[0], '');

        $generator->generate([0 => $asset1Variations[0], 2 => $asset1Variations[2]], true)->willReturn($asset1ProcessedVariations);
        $generator->generate($asset2Variations, true)->willReturn($asset2ProcessedVariations);

        $event = new AssetEvent([$asset1, $asset2]);
        $this->onAssetFilesUploaded($event)->shouldReturn($event);

        Assert::count($event->getProcessedList(), 3);
    }
}
