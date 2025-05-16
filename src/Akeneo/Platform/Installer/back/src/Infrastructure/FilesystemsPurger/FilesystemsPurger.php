<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\FilesystemsPurger;

use Akeneo\Platform\Installer\Domain\Service\FilesystemsPurgerInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FilesystemsPurger implements FilesystemsPurgerInterface
{
    public function __construct(
        private readonly JobLauncherInterface $jobLauncher,
        private readonly IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function execute(): void
    {
        $purgeFilesystemsJobInstance = $this->jobInstanceRepository->findOneByIdentifier('purge_filesystems');
        $user = $this->tokenStorage->getToken()->getUser();

        $this->jobLauncher->launch($purgeFilesystemsJobInstance, $user);
    }
}
