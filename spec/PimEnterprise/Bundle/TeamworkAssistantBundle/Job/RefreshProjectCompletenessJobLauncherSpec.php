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
        $this->beConstructedWith($jobInstanceRepository, 'job_name', '/', 'prod');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RefreshProjectCompletenessJobLauncher::class);
    }

    function it_launch_the_attribute_groups_completeness_calculation(
        $jobInstanceRepository,
        JobInstance $jobInstance,
        ProductInterface $product
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn($jobInstance);

        $product->getId()->willReturn(40);

        $this->launch($product, 'ecommerce', 'en_US')->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_job_instance_does_not_exist(
        $jobInstanceRepository,
        ProductInterface $product
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('launch', [$product, 'ecommerce', 'en_US']);
    }
}
