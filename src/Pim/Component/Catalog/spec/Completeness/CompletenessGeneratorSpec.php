<?php

namespace spec\Pim\Component\Catalog\Completeness;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\Completeness\CompletenessGenerator;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
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
                'filters'       => [['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null]],
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
                'filters' => [['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null]],
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
}
