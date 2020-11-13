<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\RefreshProjectCompleteness;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ProjectItemCalculatorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Compute the attribute group completeness for a product/channel/locale
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class RefreshProjectCompletenessTasklet implements TaskletInterface
{
    protected StepExecution $stepExecution;
    protected ProjectItemCalculatorInterface $attributeGroupCompletenessCalculator;
    protected PreProcessingRepositoryInterface $preProcessingRepository;
    protected IdentifiableObjectRepositoryInterface $localeRepository;
    protected IdentifiableObjectRepositoryInterface $channelRepository;
    protected ProductRepositoryInterface $productRepository;

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
