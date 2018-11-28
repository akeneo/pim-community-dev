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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Launcher;

use Akeneo\Pim\Automation\SuggestData\Application\Launcher\JobLauncherInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface as PimJobLauncher;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class JobLauncher implements JobLauncherInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var PimJobLauncher */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        PimJobLauncher $jobLauncher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(string $jobInstanceName, array $options = []): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($jobInstanceName);
        $user = $this->tokenStorage->getToken()->getUser();

        if (null !== $jobInstance) {
            $this->jobLauncher->launch($jobInstance, $user, $options);
        }
    }
}
