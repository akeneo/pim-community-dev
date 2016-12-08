<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectProductRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Add the product to the current project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProductCalculationStep implements CalculationStepInterface
{
    /** @var ProjectProductRepositoryInterface */
    private $projectRepository;

    /**
     * @param ProjectProductRepositoryInterface $projectRepository
     */
    public function __construct(ProjectProductRepositoryInterface $projectRepository)
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
