<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGenerator;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;

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
        Collection $completenesses,
        CompletenessInterface $newCompleteness1,
        CompletenessInterface $newCompleteness2
    ) {
        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->isEmpty()->willReturn(true);

        $calculator->calculate($product)->willReturn([$newCompleteness1, $newCompleteness2]);

        $completenesses->add($newCompleteness1)->shouldBeCalled();
        $completenesses->add($newCompleteness2)->shouldBeCalled();

        $this->generateMissingForProduct($product);
    }

    function it_generates_missing_completenesses_for_a_channel(
        $pqbFactory,
        $calculator,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel,
        CursorInterface $products,
        Collection $completenesses1,
        CompletenessInterface $newCompleteness1a,
        CompletenessInterface $newCompleteness1b,
        Collection $completenesses2,
        CompletenessInterface $newCompleteness2a
    ) {
        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->isEmpty()->willReturn(true);
        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->isEmpty()->willReturn(true);

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

        $calculator->calculate($product1)->willReturn([$newCompleteness1a, $newCompleteness1b]);
        $calculator->calculate($product2)->willReturn([$newCompleteness2a]);

        $completenesses1->add($newCompleteness1a)->shouldBeCalled();
        $completenesses1->add($newCompleteness1b)->shouldBeCalled();
        $completenesses2->add($newCompleteness2a)->shouldBeCalled();

        $this->generateMissingForChannel($channel);
    }

    function it_generates_missing_completenesses(
        $pqbFactory,
        $calculator,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        CursorInterface $products,
        Collection $completenesses1,
        CompletenessInterface $newCompleteness1a,
        CompletenessInterface $newCompleteness1b,
        Collection $completenesses2,
        CompletenessInterface $newCompleteness2a
    ) {
        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->isEmpty()->willReturn(true);
        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->isEmpty()->willReturn(true);

        $pqbFactory->create(
            [
                'filters' => [
                    ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null],
                    ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
                ],
            ]
        )->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $calculator->calculate($product1)->willReturn([$newCompleteness1a, $newCompleteness1b]);
        $calculator->calculate($product2)->willReturn([$newCompleteness2a]);

        $completenesses1->add($newCompleteness1a)->shouldBeCalled();
        $completenesses1->add($newCompleteness1b)->shouldBeCalled();
        $completenesses2->add($newCompleteness2a)->shouldBeCalled();

        $this->generateMissing();
    }

    function it_generates_missing_completenesses_for_filtered_products(
        $pqbFactory,
        $calculator,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel,
        CursorInterface $products,
        Collection $completenesses1,
        CompletenessInterface $newCompleteness1a,
        CompletenessInterface $newCompleteness1b,
        Collection $completenesses2,
        CompletenessInterface $newCompleteness2a
    ) {
        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->isEmpty()->willReturn(true);
        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->isEmpty()->willReturn(true);

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

        $calculator->calculate($product1)->willReturn([$newCompleteness1a, $newCompleteness1b]);
        $calculator->calculate($product2)->willReturn([$newCompleteness2a]);

        $completenesses1->add($newCompleteness1a)->shouldBeCalled();
        $completenesses1->add($newCompleteness1b)->shouldBeCalled();
        $completenesses2->add($newCompleteness2a)->shouldBeCalled();

        $this->generateMissingForProducts($channel, $filters);
    }
}
