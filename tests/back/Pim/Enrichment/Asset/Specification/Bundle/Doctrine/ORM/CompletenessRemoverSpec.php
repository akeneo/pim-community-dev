<?php

namespace Specification\Akeneo\Pim\Enrichment\Asset\EnrichmentBundle\Doctrine\ORM;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Asset\Bundle\AttributeType\AttributeTypes as AssetAttributeTypes;
use Akeneo\Pim\Enrichment\Asset\Bundle\Doctrine\ORM\CompletenessRemover;
use Akeneo\Asset\Component\Model\AssetInterface;
use Prophecy\Argument;

class CompletenessRemoverSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManagerInterface $entityManager,
        ProductIndexer $indexer,
        AttributeRepositoryInterface $attributeRepository,
        Connection $connection,
        BulkObjectDetacherInterface $bulkDetacher
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $entityManager,
            $indexer,
            'pim_catalog_completeness',
            $bulkDetacher,
            $attributeRepository
        );

        $entityManager->getConnection()->willReturn($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessRemover::class);
    }


    function it_removes_completeness_of_an_asset(
        $attributeRepository,
        $indexer,
        $connection,
        $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        Collection $completenesses1,
        Collection $completenesses2,
        Collection $completenesses3,
        CursorInterface $products,
        Statement $statement1,
        AssetInterface $asset
    ) {
        $connection->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.product_id IN (?)',
            [['foo', 'bar', 'baz']],
            [101]
        )->shouldBeCalled();

        $asset->getCode()->willReturn('gallery');
        $attributeRepository->getAttributeCodesByType(AssetAttributeTypes::ASSETS_COLLECTION)->willReturn(
            ['assetAttribute']
        );

        $product1->getId()->willReturn('foo');
        $product2->getId()->willReturn('bar');
        $product3->getId()->willReturn('baz');

        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, true, false);
        $products->current()->willReturn($product1, $product2, $product3);
        $products->next()->shouldBeCalled();

        $pqbFactory->create(['filters' => []])->willReturn($pqb);

        $pqb->addFilter('assetAttribute', Operators::IN_LIST, ['gallery'])->willReturn($pqb);

        $pqb->execute()->willReturn($products);

        $connection->prepare(Argument::any())->willReturn($statement1);

        $product1->getCompletenesses()->willReturn($completenesses1);
        $completenesses1->clear()->shouldBeCalled();

        $product2->getCompletenesses()->willReturn($completenesses2);
        $completenesses2->clear()->shouldBeCalled();

        $product3->getCompletenesses()->willReturn($completenesses3);
        $completenesses3->clear()->shouldBeCalled();

        $indexer->indexAll([$product1, $product2, $product3])->shouldBeCalled();

        $this->removeForAsset($asset);
    }
}
