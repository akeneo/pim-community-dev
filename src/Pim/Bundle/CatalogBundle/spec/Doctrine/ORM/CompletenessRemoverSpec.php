<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessRemover;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
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
        Connection $connection,
        BulkObjectDetacherInterface $bulkDetacher
    ) {
        $this->beConstructedWith($pqbFactory, $entityManager, $indexer, 'pim_catalog_completeness', $bulkDetacher);

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
        $product->getId()->willReturn(42);
        $product->getCompletenesses()->willReturn($completenesses);
        $completenesses->clear()->shouldBeCalled();

        $connection->prepare(Argument::any())->willReturn($statement);
        $statement->bindValue('productId', 42)->shouldBeCalled();
        $statement->execute()->shouldBeCalled();

        $indexer->index($product)->shouldBeCalled();

        $this->removeForProduct($product);
    }

    function it_removes_completeness_of_a_family(
        $indexer,
        $connection,
        $pqbFactory,
        $bulkDetacher,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        Collection $completenesses1,
        Collection $completenesses2,
        FamilyInterface $family,
        CursorInterface $products
    ) {
        $product1->getId()->willReturn(21);
        $product2->getId()->willReturn(42);

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

        $connection->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.product_id IN (?)',
            [[21, 42]],
            [Connection::PARAM_INT_ARRAY]
        )->shouldBeCalled();

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->clear()->shouldBeCalled();

        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->clear()->shouldBeCalled();

        $indexer->indexAll([$product1, $product2])->shouldBeCalled();
        $bulkDetacher->detachAll([$product1, $product2])->shouldBeCalled();

        $this->removeForFamily($family);
    }

    function it_removes_completeness_of_a_channel_locale(
        $indexer,
        $connection,
        $pqbFactory,
        $bulkDetacher,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        Collection $completenesses1,
        Collection $completenesses2,
        ChannelInterface $channel,
        LocaleInterface $locale,
        CursorInterface $products,
        CompletenessInterface $completeness1,
        CompletenessInterface $completeness2
    ) {
        $product1->getId()->willReturn(21);
        $product2->getId()->willReturn(42);

        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $channel->getCode()->willReturn('ecommerce');
        $channel->getId()->willReturn(12);
        $locale->getCode()->willReturn('en_US');
        $locale->getId()->willReturn(24);

        $pqbFactory->create(
            [
                'filters'        => [],
                'default_scope'  => 'ecommerce',
                'default_locale' => 'en_US',
            ]
        )->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $connection->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.product_id IN (?) AND c.channel_id = ? AND c.locale_id = ?',
            [[21, 42], 12, 24],
            [Connection::PARAM_INT_ARRAY, \PDO::PARAM_INT, \PDO::PARAM_INT]
        )->shouldBeCalled();

        $completeness1->getChannel()->willReturn($channel);
        $completeness1->getLocale()->willReturn($locale);
        $completeness2->getChannel()->willReturn($channel);
        $completeness2->getLocale()->willReturn($locale);

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->filter(Argument::any())->willReturn([$completeness1]);
        $completenesses1->removeElement($completeness1)->shouldBeCalled();

        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->filter(Argument::any())->willReturn([$completeness2]);
        $completenesses2->removeElement($completeness2)->shouldBeCalled();

        $indexer->indexAll([$product1, $product2])->shouldBeCalled();
        $bulkDetacher->detachAll([$product1, $product2])->shouldBeCalled();

        $this->removeForChannelAndLocale($channel, $locale);
    }
}
