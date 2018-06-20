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
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;

/**
 * Add the product to the current project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AddProductStep implements CalculationStepInterface
{
    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /**
     * @param PreProcessingRepositoryInterface $preProcessingRepository
     */
    public function __construct(PreProcessingRepositoryInterface $preProcessingRepository)
    {
        $this->preProcessingRepository = $preProcessingRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $this->preProcessingRepository->addProduct($project, $product);
    }
}
