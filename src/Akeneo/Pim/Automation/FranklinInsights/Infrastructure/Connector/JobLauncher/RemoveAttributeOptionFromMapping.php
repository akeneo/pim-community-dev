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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\RemoveAttributeOptionFromMappingInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveAttributeOptionFromMapping implements RemoveAttributeOptionFromMappingInterface
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
    public function process(string $pimAttributeCode, string $pimAttributeOptionCode): void
    {
        if (empty($pimAttributeCode) || empty($pimAttributeOptionCode)) {
            return;
        }

        $jobInstance = $this
            ->jobInstanceRepository
            ->findOneByIdentifier(JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING);
        if (null === $jobInstance) {
            // TODO: Should throw an exception. Should be done in APAI-450
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $jobParameters = [
            'pim_attribute_code' => $pimAttributeCode,
            'pim_attribute_option_code' => $pimAttributeOptionCode,
        ];

        $this->jobLauncher->launch(
            $jobInstance,
            $user,
            $jobParameters
        );
    }
}
