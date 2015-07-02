<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;

class FilteredFamilyReaderSpec extends ObjectBehavior
{
    function let(JobConfigurationRepositoryInterface $jobConfigurationRepo, FamilyRepositoryInterface $familyRepository)
    {
        $this->beConstructedWith($jobConfigurationRepo, $familyRepository);
    }

    function it_reads_families(
        $jobConfigurationRepo,
        $familyRepository,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration,
        FamilyInterface $pantFamily,
        FamilyInterface $sockFamily
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(json_encode([
            'filters' => [
                [
                    'field'    => 'id',
                    'operator' => 'IN',
                    'value'    => [12, 13, 14]
                ]
            ]
        ]));

        $families = [$pantFamily, $sockFamily];
        $familyRepository->findByIds([12, 13, 14])->willReturn($families);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->read()->shouldReturn($pantFamily);
        $this->read()->shouldReturn($sockFamily);
        $this->read()->shouldReturn(null);

        $stepExecution->incrementSummaryInfo('read')->shouldHaveBeenCalledTimes(2);
    }

    function it_throws_an_exception_if_no_job_configuration_is_found(
        $jobConfigurationRepo,
        StepExecution $stepExecution,
        JobExecution $jobExecution
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn(null);
        $this->setStepExecution($stepExecution);

        $this->shouldThrow('Doctrine\ORM\EntityNotFoundException')
            ->during('read');
    }
}
