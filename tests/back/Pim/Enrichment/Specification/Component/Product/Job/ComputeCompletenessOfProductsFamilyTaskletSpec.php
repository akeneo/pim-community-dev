<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Job\ComputeCompletenessOfProductsFamilyTasklet;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class ComputeCompletenessOfProductsFamilyTaskletSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        CursorableRepositoryInterface $productRepository,
        BulkSaverInterface $bulkProductSaver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $productQueryBuilderFactory,
            $productRepository,
            $bulkProductSaver,
            $cacheClearer
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfProductsFamilyTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_recomputes_the_completeness_of_all_the_products_belonging_the_given_family(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        CursorableRepositoryInterface $productRepository,
        BulkSaverInterface $bulkProductSaver,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        FamilyInterface $family,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $jobParameters->get('family_code')->willReturn('accessories');
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $familyRepository->findOneByIdentifier('accessories')->willReturn($family);

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $family->getCode()->willReturn('accessories');
        $pqb->addFilter('family', Operators::IN_LIST, ['accessories'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->willReturn(
            new IdentifierResult('identifier1', ProductInterface::class),
            new IdentifierResult('identifier2', ProductInterface::class),
            new IdentifierResult('identifier3', ProductInterface::class),
        );
        $cursor->next()->shouldBeCalled();
        $cursor->rewind()->shouldBeCalled();

        $productRepository->getItemsFromIdentifiers(['identifier1', 'identifier2', 'identifier3'])->willReturn(
            [$product1, $product2, $product3]
        );

        $bulkProductSaver->saveAll([$product1, $product2, $product3], ['force_save' => true])->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_does_not_recompute_if_the_given_family_code_is_invalid(
        IdentifiableObjectRepositoryInterface $familyRepository,
        BulkSaverInterface $bulkProductSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('family_code')->willReturn('unknown_family');
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $familyRepository->findOneByIdentifier('unknown_family')->willReturn(null);

        $bulkProductSaver->saveAll()->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->shouldThrow(new \InvalidArgumentException('Family not found, "unknown_family" given'))
            ->during('execute');
    }
}
