<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobLauncher\RemoveAttributesFromMapping;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RemoveAttributesFromMappingSpec extends ObjectBehavior
{
    public function let(
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($jobInstanceRepository, $jobLauncher, $tokenStorage);
    }

    public function it_is_a_remove_attributes_from_mapping(): void
    {
        $this->shouldHaveType(RemoveAttributesFromMapping::class);
    }

    public function it_implements_the_remove_attributes_from_mapping(): void
    {
        $this->shouldImplement(RemoveAttributesFromMappingInterface::class);
    }

    public function it_does_not_launch_the_job_if_it_does_not_find_the_job_instance(
        $jobInstanceRepository,
        $jobLauncher
    ): void {
        $jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING)
            ->willReturn(null);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();
        $this->process(['my_family'], ['my_attribute']);
    }

    public function it_does_not_launch_the_job_when_families_or_attributes_are_empty(
        $jobInstanceRepository,
        $tokenStorage,
        $jobLauncher,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobLauncher->launch($jobInstance, $user, Argument::any())->shouldNotBeCalled();

        $this->process(['my_family'], []);
        $this->process([], []);
        $this->process([], ['my_attributes']);
    }

    public function it_launches_a_job_per_family(
        $jobInstanceRepository,
        $tokenStorage,
        $jobLauncher,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobParameters = [
            'pim_attribute_codes' => ['my_attribute_1'],
            'family_code' => 'my_family',
        ];
        $jobLauncher->launch($jobInstance, $user, $jobParameters)->shouldBeCalled();

        $this->process(['my_family'], ['my_attribute_1']);
    }
}
