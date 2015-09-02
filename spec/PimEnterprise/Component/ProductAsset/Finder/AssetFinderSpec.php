<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Component\ProductAsset\Finder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * TODO localizable specs
 */
class AssetFinderSpec extends ObjectBehavior
{
    public function let(
        VariationRepositoryInterface $variationsRepository
    ) {
        $this->beConstructedWith($variationsRepository);
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Finder\AssetFinder');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface');
    }

    public function it_retrieves_a_reference(
        AssetInterface $asset,
        ReferenceInterface $reference
    ) {
        $asset->getReference(null)->willReturn($reference);
        $this->retrieveReference($asset)->shouldReturn($reference);
    }

    public function it_throws_exception_for_unknown_reference(
        AssetInterface $asset
    ) {
        $asset->getReference(null)->willReturn(null);
        $asset->getCode()->willReturn('foo');
        $this->shouldThrow('\LogicException')->during('retrieveReference', [$asset]);
    }

    public function it_retrieves_a_variation(
        ReferenceInterface $reference,
        VariationInterface $variation,
        ChannelInterface $channel
    ) {
        $reference->getVariation($channel)->willReturn($variation);
        $this->retrieveVariation($reference, $channel)->shouldReturn($variation);
    }

    public function it_throws_exception_for_unknown_variation(
        ReferenceInterface $reference,
        ChannelInterface $channel
    ) {
        $reference->getVariation($channel)->willReturn(null);
        $reference->getId()->willReturn(1);
        $channel->getCode()->willReturn('foo');
        $this->shouldThrow('\LogicException')->during('retrieveVariation', [$reference, $channel]);
    }

    public function it_retrieves_variations_missing_files_for_an_asset(
        AssetInterface $asset,
        VariationInterface $variation1,
        VariationInterface $variation2,
        VariationInterface $variation3
    ) {
        $variation1->getFileInfo()->willReturn(null);
        $variation1->getSourceFileInfo()->willReturn('not null');

        $variation2->getFileInfo()->willReturn(null);
        $variation2->getSourceFileInfo()->willReturn(null);

        $variation3->getFileInfo()->willReturn(null);
        $variation3->getSourceFileInfo()->willReturn('not null');

        $missingVariations = [
            $variation1,
            $variation3
        ];
        $asset->getVariations()->willReturn([$variation1, $variation2, $variation3]);
        $this->retrieveVariationsNotGenerated($asset)->shouldReturn($missingVariations);
    }

    public function it_retrieves_all_variations_missing_file(
        $variationsRepository,
        VariationInterface $variation1,
        VariationInterface $variation2
    ) {
        $variation1->getFileInfo()->willReturn(null);
        $variation1->getSourceFileInfo()->willReturn('not null');

        $variation2->getFileInfo()->willReturn(null);
        $variation2->getSourceFileInfo()->willReturn(null);

        $missingVariations = [
            $variation1,
            $variation2
        ];

        $variationsRepository->findNotGenerated()->willReturn([$variation1, $variation2]);

        $this->retrieveVariationsNotGenerated()->shouldReturn($missingVariations);
    }
}
