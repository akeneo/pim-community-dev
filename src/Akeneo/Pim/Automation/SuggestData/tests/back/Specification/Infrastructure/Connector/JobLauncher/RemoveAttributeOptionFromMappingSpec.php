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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\RemoveAttributeOptionFromMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobLauncher\RemoveAttributeOptionFromMapping;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveAttributeOptionFromMappingSpec extends ObjectBehavior
{
    public function let(
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($jobInstanceRepository, $jobLauncher, $tokenStorage);
    }

    public function it_is_a_remove_attribute_option_from_mapping(): void
    {
        $this->shouldHaveType(RemoveAttributeOptionFromMapping::class);
    }

    public function it_implements_the_remove_attribute_option_from_mapping(): void
    {
        $this->shouldImplement(RemoveAttributeOptionFromMappingInterface::class);
    }

    public function it_does_not_launch_the_job_if_it_does_not_find_the_job_instance(
        $jobInstanceRepository,
        $jobLauncher
    ): void {
        $jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING)
            ->willReturn(null);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();
        $this->process('my_pim_attribute', 'my_pim_option');
    }

    public function it_launches_a_job(
        $jobInstanceRepository,
        $tokenStorage,
        $jobLauncher,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobParameters = [
            'pim_attribute_code' => 'my_pim_attribute',
            'pim_attribute_option_code' => 'my_pim_option',
        ];
        $jobLauncher->launch($jobInstance, $user, $jobParameters)->shouldBeCalled();

        $this->process('my_pim_attribute', 'my_pim_option');
    }
}
