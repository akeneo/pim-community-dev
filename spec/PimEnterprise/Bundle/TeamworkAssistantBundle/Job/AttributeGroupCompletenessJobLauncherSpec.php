<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Job\AttributeGroupCompletenessJobLauncher;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AttributeGroupCompletenessJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobLauncherInterface $simpleJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($simpleJobLauncher, $jobInstanceRepository, $tokenStorage, 'job_name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupCompletenessJobLauncher::class);
    }

    function it_launch_the_attribute_groups_completeness_calculation(
        $jobInstanceRepository,
        $tokenStorage,
        $simpleJobLauncher,
        JobInstance $jobInstance,
        ProductInterface $product,
        TokenInterface $token,
        UserInterface $user
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn($jobInstance);

        $product->getId()->willReturn(40);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $simpleJobLauncher->launch($jobInstance, $user, [
            'product_identifier' => 40,
            'channel_identifier' => 'ecommerce',
            'locale_identifier' => 'en_US',
        ])->shouldBeCalled();

        $this->launch($product, 'ecommerce', 'en_US')->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_user_does_not_exist(
        $tokenStorage,
        ProductInterface $product
    ) {
        $tokenStorage->getToken()->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('launch', [$product, 'ecommerce', 'en_US']);
    }

    function it_throws_an_exception_if_the_job_instance_does_not_exist(
        $jobInstanceRepository,
        $tokenStorage,
        ProductInterface $product,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('launch', [$product, 'ecommerce', 'en_US']);
    }
}
