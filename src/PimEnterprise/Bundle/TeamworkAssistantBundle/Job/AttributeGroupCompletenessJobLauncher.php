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
class AttributeGroupCompletenessJobLauncher
{
    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var string */
    protected $attributeGroupCompletenessJobName;

    /**
     * @param JobLauncherInterface  $simpleJobLauncher
     * @param JobInstanceRepository $jobInstanceRepository
     * @param TokenStorageInterface $tokenStorage
     * @param string                $attributeGroupCompletenessJobName
     */
    public function __construct(
        JobLauncherInterface $simpleJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        $attributeGroupCompletenessJobName
    ) {
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->tokenStorage = $tokenStorage;
        $this->attributeGroupCompletenessJobName = $attributeGroupCompletenessJobName;
    }

    /**
     * @param ProductInterface $product
     * @param string           $channel
     * @param string           $locale
     */
    public function launch(ProductInterface $product, $channel, $locale)
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new \RuntimeException(
                'Cannot run refresh project completeness, there is no available security token'
            );
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->attributeGroupCompletenessJobName);
        if (null === $jobInstance) {
            throw new \RuntimeException('Cannot run refresh project completeness, there is no available job');
        }

        $this->simpleJobLauncher->launch($jobInstance, $token->getUser(), [
            'product_identifier' => $product->getId(),
            'channel_identifier' => $channel,
            'locale_identifier'  => $locale,
        ]);
    }
}
