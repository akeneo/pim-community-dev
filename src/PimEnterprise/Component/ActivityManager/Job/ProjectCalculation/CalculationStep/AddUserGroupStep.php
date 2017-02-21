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
use PimEnterprise\Component\ActivityManager\Calculator\ProjectCalculatorInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * Find contributor groups (user groups which have edit on the product) affected by the project and
 * add them to the project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AddUserGroupStep implements CalculationStepInterface
{
    /** @var ProjectCalculatorInterface */
    protected $contributorGroupCalculator;

    /**
     * @param ProjectCalculatorInterface $contributorGroupCalculator
     */
    public function __construct(ProjectCalculatorInterface $contributorGroupCalculator)
    {
        $this->contributorGroupCalculator = $contributorGroupCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $contributorGroups = $this->contributorGroupCalculator->calculate($project, $product);

        foreach ($contributorGroups as $contributorGroup) {
            $project->addUserGroup($contributorGroup);
        }
    }
}
