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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Launcher;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class JobLauncherSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith($jobInstanceRepository, $jobLauncher, $tokenStorage);
    }

    public function it_is_a_job_launcher(): void
    {
        $this->shouldHaveType(JobLauncher::class);
        $this->shouldImplement(\Akeneo\Pim\Automation\SuggestData\Application\Connector\JobLauncherInterface::class);
    }

    public function it_launches_a_job(
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        $jobInstanceRepository,
        $tokenStorage,
        $jobLauncher
    ): void {
        $jobInstanceName = JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING;

        $jobInstanceRepository
            ->findOneByIdentifier($jobInstanceName)
            ->willReturn($jobInstance);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobLauncher->launch($jobInstance, $user, [
            'argument_1' => 'value_1',
            'argument_2' => 'value_2',
        ])->shouldBeCalled();

        $this->launch($jobInstanceName, [
            'argument_1' => 'value_1',
            'argument_2' => 'value_2',
        ]);
    }
}
