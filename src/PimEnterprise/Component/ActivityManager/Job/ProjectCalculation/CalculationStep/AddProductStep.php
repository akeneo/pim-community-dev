<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

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
