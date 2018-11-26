<?php

namespace spec\PimEnterprise\Component\ProductAsset\Job;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use PimEnterprise\Component\ProductAsset\Job\ComputeCompletenessOfProductsLinkedToAssetsTasklet;

class ComputeCompletenessOfProductsLinkedToAssetsTaskletSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        EntityManagerInterface $entityManager,
        BulkIndexerInterface $indexer,
        BulkObjectDetacherInterface $bulkDetacher,
        StepExecution $stepExecution
    ): void {
        $this->beConstructedWith(
            $attributeRepository,
            $productQueryBuilderFactory,
            $entityManager,
            $indexer,
            $bulkDetacher,
            'pim_catalog_completeness'
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_compute_completeness_of_products_linked_to_assets_tasklet(): void
    {
        $this->shouldBeAnInstanceOf(ComputeCompletenessOfProductsLinkedToAssetsTasklet::class);
    }

    function it_is_a_tasklet(): void
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_resets_completenesses_of_products_linked_to_assets(
        $attributeRepository,
        $productQueryBuilderFactory,
        $entityManager,
        $indexer,
        $bulkDetacher,
        $stepExecution,
        Connection $connection,
        ProductQueryBuilderInterface $pqb,
        JobParameters $jobParameters,
        CursorInterface $cursor,
        ProductInterface $product123,
        Collection $completenesses123,
        ProductInterface $product456,
        Collection $completenesses456
    ): void {
        $productQueryBuilderFactory->create()->willReturn($pqb);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('asset_codes')->willReturn(['asset_code_1', 'asset_code_2']);

        $product123->getId()->willReturn(123);
        $product123->getCompletenesses()->willReturn($completenesses123);
        $product456->getId()->willReturn(456);
        $product456->getCompletenesses()->willReturn($completenesses456);
        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, false);
        $cursor->current()->willReturn($product123, $product456);
        $cursor->next()->shouldBeCalled();

        $attributeRepository->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION)->willReturn(['assets']);
        $pqb->addFilter('assets', Operators::IN_LIST, ['asset_code_1', 'asset_code_2'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $entityManager->getConnection()->willReturn($connection);

        $completenesses123->clear()->shouldBeCalled();
        $completenesses456->clear()->shouldBeCalled();

        $connection->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.product_id IN (:productIds)',
            ['productIds' => [123, 456]],
            ['productIds' => Connection::PARAM_INT_ARRAY]
        )->shouldBeCalled();

        $indexer->indexAll([$product123, $product456]);
        $bulkDetacher->detachAll([$product123, $product456])->shouldBeCalled();

        $this->execute()->shouldReturn(null);
    }
}
