<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

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
        $families = [$pantFamily, $sockFamily];
        $familyRepository->findByIds([12, 13, 14])->willReturn($families);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->read()->shouldReturn($pantFamily);
        $this->read()->shouldReturn($sockFamily);
        $this->read()->shouldReturn(null);

        $stepExecution->incrementSummaryInfo('read')->shouldHaveBeenCalledTimes(2);
    }
}
