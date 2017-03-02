<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Job\AttributeGroupCompleteness;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Job\AttributeGroupCompletenessJobLauncher;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;

/**
 * Compute the attribute group completeness for a product/channel/locale
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AttributeGroupCompletenessTasklet implements TaskletInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var AttributeGroupCompletenessJobLauncher */
    protected $attributeGroupCompletenessCalculator;

    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /**
     * @param ProjectItemCalculatorInterface        $attributeGroupCompletenessCalculator
     * @param PreProcessingRepositoryInterface      $preProcessingRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param ProductRepositoryInterface            $productRepository
     */
    public function __construct(
        ProjectItemCalculatorInterface $attributeGroupCompletenessCalculator,
        PreProcessingRepositoryInterface $preProcessingRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->attributeGroupCompletenessCalculator = $attributeGroupCompletenessCalculator;
        $this->preProcessingRepository = $preProcessingRepository;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $product = $this->productRepository->find($jobParameters->get('product_identifier'));

        if (!$this->preProcessingRepository->belongsToAProject($product)) {
            return;
        }

        $channel = $this->channelRepository->findOneByIdentifier($jobParameters->get('channel_identifier'));
        $locale = $this->localeRepository->findOneByIdentifier($jobParameters->get('locale_identifier'));

        $attributeGroupCompleteness = $this->attributeGroupCompletenessCalculator->calculate(
            $product,
            $channel,
            $locale
        );

        $this->preProcessingRepository->addAttributeGroupCompleteness(
            $product,
            $channel,
            $locale,
            $attributeGroupCompleteness
        );
    }
}
