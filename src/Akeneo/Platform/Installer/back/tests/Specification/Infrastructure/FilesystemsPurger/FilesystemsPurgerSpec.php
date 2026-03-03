<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Installer\Infrastructure\FilesystemsPurger;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FilesystemsPurgerSpec extends ObjectBehavior
{
    public function let(
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
    ) {
        $this->beConstructedWith($jobLauncher, $jobInstanceRepository, $tokenStorage);
    }

    public function it_launch_purge_filesystems_job(
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        JobInstance $purgeFilesystemsJobInstance,
        TokenInterface $token,
        UserInterface $user,
    ): void {
        $jobInstanceRepository->findOneByIdentifier('purge_filesystems')->willReturn($purgeFilesystemsJobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobLauncher->launch($purgeFilesystemsJobInstance, $user)->shouldBeCalled();

        $this->execute();
    }
}
