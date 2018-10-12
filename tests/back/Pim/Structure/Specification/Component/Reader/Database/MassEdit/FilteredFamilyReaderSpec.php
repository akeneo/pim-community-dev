<?php

namespace Specification\Akeneo\Pim\Structure\Component\Reader\Database\MassEdit;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class FilteredFamilyReaderSpec extends ObjectBehavior
{
    function let(FamilyRepositoryInterface $familyRepository)
    {
        $this->beConstructedWith($familyRepository);
    }

    function it_reads_families(
        $familyRepository,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        FamilyInterface $pantFamily,
        FamilyInterface $sockFamily,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn(
            [
                [
                    'field'    => 'id',
                    'operator' => 'IN',
                    'value'    => [12, 13, 14]
                ]
            ]
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $familyRepository->find(12)->willReturn($pantFamily);
        $familyRepository->find(13)->willReturn(null);
        $familyRepository->find(14)->willReturn($sockFamily);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->read()->shouldReturn($pantFamily);
        $this->read()->shouldReturn($sockFamily);
        $this->read()->shouldReturn(null);

        $stepExecution->incrementSummaryInfo('read')->shouldHaveBeenCalledTimes(2);
    }
}
