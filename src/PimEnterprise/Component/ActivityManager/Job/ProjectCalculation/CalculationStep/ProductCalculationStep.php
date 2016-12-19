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
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;

/**
 * Add the product to the current project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProductCalculationStep implements CalculationStepInterface
{
    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /**
     * @param ProjectRepositoryInterface $projectRepository
     */
    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $this->projectRepository->addProduct($project, $product);
    }
}
