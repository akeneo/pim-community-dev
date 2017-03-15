<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Component\Batch\Model\JobInstance;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Job\RefreshProjectCompletenessJobLauncher;
use PhpSpec\ObjectBehavior;

class RefreshProjectCompletenessJobLauncherSpec extends ObjectBehavior
{
    function let(JobInstanceRepository $jobInstanceRepository)
    {
        $this->beConstructedWith($jobInstanceRepository, 'job_name', '/tmp', 'prod');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RefreshProjectCompletenessJobLauncher::class);
    }

    function it_throws_an_exception_if_the_job_instance_does_not_exist(
        $jobInstanceRepository,
        ProductInterface $product
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('launch', [$product, 'ecommerce', 'en_US']);
    }
}
