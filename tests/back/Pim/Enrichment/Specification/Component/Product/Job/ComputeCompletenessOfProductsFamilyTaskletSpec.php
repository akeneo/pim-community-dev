<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
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
        BulkSaverInterface $bulkProductSaver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $productQueryBuilderFactory,
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
        $familyRepository,
        $productQueryBuilderFactory,
        $bulkProductSaver,
        $cacheClearer,
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
        $cursor->current()->willReturn($product1, $product2, $product3);
        $cursor->next()->shouldBeCalled();
        $cursor->rewind()->shouldBeCalled();

        $bulkProductSaver->saveAll([$product1, $product2, $product3])->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_does_not_recompute_if_the_given_family_code_is_invalid(
        $familyRepository,
        $bulkProductSaver,
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
