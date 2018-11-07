<?php

namespace spec\Pim\Component\Catalog\Completeness;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\Completeness\CompletenessGenerator;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
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

    function it_generates_missing_completenesses_for_a_channel(
        $pqbFactory,
        $calculator,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel,
        CursorInterface $products,
        CompletenessInterface $newCompleteness1a,
        CompletenessInterface $newCompleteness1b,
        CompletenessInterface $newCompleteness2a,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        ChannelInterface $channel1
    ) {
        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $completenesses1 = new ArrayCollection();
        $completenesses2 = new ArrayCollection();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $product2->getCompletenesses()->willReturn($completenesses2);

        $channel->getCode()->willReturn('ecommerce');

        $pqbFactory->create(
            [
                'filters'       => [
                    ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null],
                    ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
                ],
                'default_scope' => 'ecommerce'
            ]
        )->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $locale1->getId()->willReturn(1);
        $locale2->getId()->willReturn(2);
        $channel1->getId()->willReturn(1);

        $newCompleteness1a->getLocale()->willReturn($locale1);
        $newCompleteness1a->getChannel()->willReturn($channel1);
        $newCompleteness1b->getLocale()->willReturn($locale2);
        $newCompleteness1b->getChannel()->willReturn($channel1);

        $newCompleteness2a->getLocale()->willReturn($locale1);
        $newCompleteness2a->getChannel()->willReturn($channel1);

        $calculator->calculate($product1)->willReturn([$newCompleteness1a, $newCompleteness1b]);
        $calculator->calculate($product2)->willReturn([$newCompleteness2a]);

        $this->generateMissingForChannel($channel);

        Assert::count($completenesses1, 2);
        Assert::count($completenesses2, 1);
    }

    function it_generates_missing_completenesses(
        $pqbFactory,
        $calculator,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        CursorInterface $products,
        CompletenessInterface $newCompleteness1a,
        CompletenessInterface $newCompleteness1b,
        CompletenessInterface $newCompleteness2a,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        ChannelInterface $channel1
    ) {
        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $completenesses1 = new ArrayCollection();
        $completenesses2 = new ArrayCollection();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $product2->getCompletenesses()->willReturn($completenesses2);

        $pqbFactory->create(
            [
                'filters' => [
                    ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null],
                    ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
                ],
            ]
        )->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $locale1->getId()->willReturn(1);
        $locale2->getId()->willReturn(2);
        $channel1->getId()->willReturn(1);

        $newCompleteness1a->getLocale()->willReturn($locale1);
        $newCompleteness1a->getChannel()->willReturn($channel1);
        $newCompleteness1b->getLocale()->willReturn($locale2);
        $newCompleteness1b->getChannel()->willReturn($channel1);

        $newCompleteness2a->getLocale()->willReturn($locale1);
        $newCompleteness2a->getChannel()->willReturn($channel1);

        $calculator->calculate($product1)->willReturn([$newCompleteness1a, $newCompleteness1b]);
        $calculator->calculate($product2)->willReturn([$newCompleteness2a]);

        $this->generateMissing();

        Assert::count($completenesses2, 1);
        Assert::count($completenesses1, 2);
    }

    function it_generates_missing_completenesses_for_filtered_products(
        $pqbFactory,
        $calculator,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel,
        CursorInterface $products,
        CompletenessInterface $newCompleteness1a,
        CompletenessInterface $newCompleteness1b,
        CompletenessInterface $newCompleteness2a,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        ChannelInterface $channel1
    ) {
        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $completenesses1 = new ArrayCollection();
        $completenesses2 = new ArrayCollection();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $product2->getCompletenesses()->willReturn($completenesses2);

        $channel->getCode()->willReturn('ecommerce');

        $filters = [
            ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null]
        ];

        $pqbFactory->create(
            [
                'filters'       => $filters,
                'default_scope' => 'ecommerce'
            ]
        )->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $locale1->getId()->willReturn(1);
        $locale2->getId()->willReturn(2);
        $channel1->getId()->willReturn(1);

        $newCompleteness1a->getLocale()->willReturn($locale1);
        $newCompleteness1a->getChannel()->willReturn($channel1);
        $newCompleteness1b->getLocale()->willReturn($locale2);
        $newCompleteness1b->getChannel()->willReturn($channel1);

        $newCompleteness2a->getLocale()->willReturn($locale1);
        $newCompleteness2a->getChannel()->willReturn($channel1);

        $calculator->calculate($product1)->willReturn([$newCompleteness1a, $newCompleteness1b]);
        $calculator->calculate($product2)->willReturn([$newCompleteness2a]);

        $this->generateMissingForProducts($channel, $filters);

        Assert::count($completenesses2, 1);
        Assert::count($completenesses1, 2);
    }
}
