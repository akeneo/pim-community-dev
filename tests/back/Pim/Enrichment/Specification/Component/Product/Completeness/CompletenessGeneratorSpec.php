<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGenerator;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class CompletenessGeneratorSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessCalculatorInterface $calculator,
        GetProductCompletenesses $getProductCompletenesses,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $calculator,
            $getProductCompletenesses,
            $channelRepository,
            $localeRepository,
            $attributeRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessGenerator::class);
    }

    function it_generates_missing_completeness_for_a_product(
        $calculator,
        $channelRepository,
        $localeRepository,
        ProductInterface $product,
        CompletenessInterface $newCompleteness1v2,
        CompletenessInterface $newCompleteness2v2,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        ChannelInterface $channel
    ) {
        $completenesses = new ArrayCollection();
        $product->getCompletenesses()->willReturn($completenesses);

        $locale1->getId()->willReturn(1);
        $locale2->getId()->willReturn(2);
        $channel->getId()->willReturn(1);

        $newCompleteness1v3 = new ProductCompleteness('ecommerce', 'en_US', 1, []);
        $newCompleteness2v3 = new ProductCompleteness('ecommerce', 'fr_FR', 1, []);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale1);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($locale2);

        $newCompleteness1v2->getLocale()->willReturn($locale1);
        $newCompleteness1v2->getChannel()->willReturn($channel);

        $newCompleteness2v2->getLocale()->willReturn($locale2);
        $newCompleteness2v2->getChannel()->willReturn($channel);

        $calculator->calculate($product)->willReturn([$newCompleteness1v3, $newCompleteness2v3]);

        $this->generateMissingForProduct($product);

        Assert::count($completenesses, 2);
    }
}
