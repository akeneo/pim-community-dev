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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\SuggestData\Application\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RemoveAttributesFromMapping implements RemoveAttributesFromMappingInterface
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
    public function process(array $familyCodes, array $removedAttributes): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING);
        if (null === $jobInstance) {
            // TODO: Should throw an exception. Should be done in APAI-450
            return;
        }
        $user = $this->tokenStorage->getToken()->getUser();

        if (empty($familyCodes) || empty($removedAttributes)) {
            return;
        }
        foreach ($familyCodes as $familyCode) {
            $jobParameters = [
                'pim_attribute_codes' => $removedAttributes,
                'family_code' => $familyCode,
            ];

            $this->jobLauncher->launch(
                $jobInstance,
                $user,
                $jobParameters
            );
        }
    }
}
