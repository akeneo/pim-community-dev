<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Job\RefreshProjectCompletenessJobLauncher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshProjectCompletenessJobLauncherSpec extends ObjectBehavior
{
    function let(JobLauncherInterface $jobLauncher, TokenStorageInterface $tokenStorage, JobInstanceRepository $jobInstanceRepository)
    {
        $this->beConstructedWith($jobLauncher, $tokenStorage, $jobInstanceRepository, 'job_name');
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

    function it_launches_a_job(
        $jobLauncher,
        $tokenStorage,
        $jobInstanceRepository,
        ProductInterface $product,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn($jobInstance);
        $product->getId()->willReturn(1);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $configuration = [
            'product_identifier' => 1,
            'channel_identifier' => 'channel',
            'locale_identifier'  => 'locale',
        ];

        $jobLauncher->launch($jobInstance, $user, $configuration)->shouldBeCalled();

        $this->launch($product, 'channel', 'locale');
    }
}
