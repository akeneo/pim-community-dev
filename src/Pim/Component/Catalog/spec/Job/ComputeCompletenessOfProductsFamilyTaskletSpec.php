<?php

namespace spec\Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Job\ComputeCompletenessOfProductsFamilyTasklet;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

class ComputeCompletenessOfProductsFamilyTaskletSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyRepository,
        CompletenessManager $completenessManager,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkSaverInterface $bulkProductSaver,
        BulkObjectDetacherInterface $bulkObjectDetacher
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $completenessManager,
            $productQueryBuilderFactory,
            $bulkProductSaver,
            $bulkObjectDetacher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfProductsFamilyTasklet::class);
    }

    function it_calls_recomputes_the_completeness_of_all_the_products_belonging_the_given_family(
        $familyRepository,
        $completenessManager,
        $productQueryBuilderFactory,
        $bulkProductSaver,
        $bulkObjectDetacher,
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

        $completenessManager->scheduleForFamily($family)->shouldBeCalled();

        $productQueryBuilderFactory->create()->willReturn($pqb);
        $family->getCode()->willReturn('accessories');
        $pqb->addFilter('family', Operators::IN_LIST, ['accessories'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->willReturn($product1, $product2, $product3);
        $cursor->next()->shouldBeCalled();
        $cursor->rewind()->shouldBeCalled();

        $bulkProductSaver->saveAll([$product1, $product2, $product3])->shouldBeCalled();
        $bulkObjectDetacher->detachAll([$product1, $product2, $product3])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_does_not_recompute_if_the_given_family_code_is_invalid(
        $familyRepository,
        $completenessManager,
        $bulkProductSaver,
        $bulkObjectDetacher,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('family_code')->willReturn('unknown_family');
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $familyRepository->findOneByIdentifier('unknown_family')->willReturn(null);

        $completenessManager->scheduleForFamily()->shouldNotBeCalled();
        $bulkProductSaver->saveAll()->shouldNotBeCalled();
        $bulkObjectDetacher->detachAll()->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }
}
