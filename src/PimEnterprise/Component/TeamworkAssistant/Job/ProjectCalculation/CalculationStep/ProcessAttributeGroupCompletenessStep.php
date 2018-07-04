<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;

/**
 * Fill in the sql table used to return the project completeness.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProcessAttributeGroupCompletenessStep implements CalculationStepInterface
{
    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /** @var ProjectItemCalculatorInterface */
    protected $attributeGroupCompletenessCalculator;

    /**
     * @param PreProcessingRepositoryInterface $preProcessingRepository
     * @param ProjectItemCalculatorInterface   $attributeGroupCompletenessCalculator
     */
    public function __construct(
        PreProcessingRepositoryInterface $preProcessingRepository,
        ProjectItemCalculatorInterface $attributeGroupCompletenessCalculator
    ) {
        $this->preProcessingRepository = $preProcessingRepository;
        $this->attributeGroupCompletenessCalculator = $attributeGroupCompletenessCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        if (false === $this->preProcessingRepository->isProcessableAttributeGroupCompleteness($product, $project)) {
            return null;
        }

        $channel = $project->getChannel();
        $locale = $project->getLocale();

        $attributeGroupCompleteness = $this->attributeGroupCompletenessCalculator
            ->calculate($product, $channel, $locale);

        $this->preProcessingRepository->addAttributeGroupCompleteness(
            $product,
            $channel,
            $locale,
            $attributeGroupCompleteness
        );
    }
}
