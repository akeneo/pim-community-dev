<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\IdentifyProductsToResubscribeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class IdentifyProductsToResubscribe implements IdentifyProductsToResubscribeInterface
{
    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param JobInstanceRepository $jobInstanceRepository
     * @param JobLauncherInterface $jobLauncher
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $franklinIdentifierCodes): void
    {
        if (empty($franklinIdentifierCodes)) {
            return;
        }

        $jobInstance = $this
            ->jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE);
        if (null === $jobInstance) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $this->jobLauncher->launch(
            $jobInstance,
            $user,
            ['updated_identifiers' => $franklinIdentifierCodes]
        );
    }
}
