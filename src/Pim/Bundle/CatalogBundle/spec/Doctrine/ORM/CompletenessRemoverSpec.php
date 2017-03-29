<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessRemover;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\ProductIndexer;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;

class CompletenessRemoverSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManagerInterface $entityManager,
        ProductIndexer $indexer,
        Connection $connection
    ) {
        $this->beConstructedWith($pqbFactory, $entityManager, $indexer);

        $entityManager->getConnection()->willReturn($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessRemover::class);
    }

    function it_removes_completeness_of_a_product(
        $indexer,
        $connection,
        ProductInterface $product,
        Collection $completenesses,
        Statement $statement
    ) {
        $product->getIdentifier()->willReturn('foo');
        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->clear()->shouldBeCalled();

        $connection->executeQuery(Argument::any(), ['foo'])->willReturn($statement);
        $statement->execute()->shouldBeCalled();

        $indexer->index($product)->shouldBeCalled();

        $this->removeForProduct($product);
    }

    function it_removes_completeness_of_a_family(
        $indexer,
        $connection,
        $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        Collection $completenesses1,
        Collection $completenesses2,
        FamilyInterface $family,
        CursorInterface $products,
        Statement $statement
    ) {
        $product1->getIdentifier()->willReturn('foo');
        $product2->getIdentifier()->willReturn('bar');

        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $family->getCode()->willReturn('pants');

        $pqbFactory->create(
            [
                'filters' => [
                    ['field' => 'family', 'operator' => Operators::IN_LIST, 'value' => ['pants']],
                ],
            ]
        )->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $connection->prepare(Argument::any())->willReturn($statement);
        $statement->bindValue('identifiers', ['foo', 'bar'], Type::SIMPLE_ARRAY)->shouldBeCalled();
        $statement->execute()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->clear()->shouldBeCalled();

        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->clear()->shouldBeCalled();

        $indexer->indexAll([$product1, $product2])->shouldBeCalled();

        $this->removeForFamily($family);
    }

    function it_removes_completeness_of_a_channel_locale(
        $indexer,
        $connection,
        $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        Collection $completenesses1,
        Collection $completenesses2,
        ChannelInterface $channel,
        LocaleInterface $locale,
        CursorInterface $products,
        Statement $statement
    ) {
        $product1->getIdentifier()->willReturn('foo');
        $product2->getIdentifier()->willReturn('bar');

        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $channel->getCode()->willReturn('ecommerce');
        $locale->getCode()->willReturn('en_US');

        $pqbFactory->create(
            [
                'filters'        => [],
                'default_scope'  => 'ecommerce',
                'default_locale' => 'en_US',
            ]
        )->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $connection->prepare(Argument::any())->willReturn($statement);
        $statement->bindValue('identifiers', ['foo', 'bar'], Type::SIMPLE_ARRAY)->shouldBeCalled();
        $statement->execute()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->clear()->shouldBeCalled();

        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->clear()->shouldBeCalled();

        $indexer->indexAll([$product1, $product2])->shouldBeCalled();

        $this->removeForChannelAndLocale($channel, $locale);
    }
}
