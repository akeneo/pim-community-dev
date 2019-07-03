<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGenerator;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class CompletenessGeneratorSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessCalculatorInterface $calculator
    ) {
        $this->beConstructedWith($pqbFactory, $calculator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessGenerator::class);
    }

    function it_generates_missing_completeness_for_a_product(
        $calculator,
        ProductInterface $product,
        CompletenessInterface $newCompleteness1,
        CompletenessInterface $newCompleteness2,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        ChannelInterface $channel
    ) {
        $completenesses = new ArrayCollection();
        $product->getCompletenesses()->willReturn($completenesses);

        $locale1->getId()->willReturn(1);
        $locale2->getId()->willReturn(2);
        $channel->getId()->willReturn(1);

        $newCompleteness1->getLocale()->willReturn($locale1);
        $newCompleteness1->getChannel()->willReturn($channel);

        $newCompleteness2->getLocale()->willReturn($locale2);
        $newCompleteness2->getChannel()->willReturn($channel);

        $calculator->calculate($product)->willReturn([$newCompleteness1, $newCompleteness2]);

        $this->generateMissingForProduct($product);

        Assert::count($completenesses, 2);
    }
}
