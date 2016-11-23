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
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProductCalculationStep implements CalculationStepInterface
{
    /** @var ProjectRepositoryInterface */
    private $projectRepository;

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
