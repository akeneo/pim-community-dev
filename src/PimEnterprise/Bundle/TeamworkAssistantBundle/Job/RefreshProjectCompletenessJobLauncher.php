<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Launch the attribute group completeness calculation job
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class RefreshProjectCompletenessJobLauncher
{
    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /** @var string */
    protected $jobName;

    /**
     * @param JobLauncherInterface  $jobLauncher
     * @param TokenStorageInterface $tokenStorage
     * @param JobInstanceRepository $jobInstanceRepository
     * @param string                $jobName
     */
    public function __construct(
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        string $jobName
    ) {
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobName = $jobName;
    }

    /**
     * @param ProductInterface $product
     * @param string           $channel
     * @param string           $locale
     *
     * @throws \RuntimeException
     */
    public function launch(ProductInterface $product, $channel, $locale)
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        if (null === $jobInstance) {
            throw new \RuntimeException('Cannot run refresh project completeness, there is no available job');
        }

        $configuration = [
            'product_identifier' => $product->getId(),
            'channel_identifier' => $channel,
            'locale_identifier'  => $locale,
        ];

        $user = $this->tokenStorage->getToken()->getUser();

        $this->jobLauncher->launch($jobInstance, $user, $configuration);
    }
}
