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

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

/**
 * Find contributor groups (user groups which have edit on the product) affected by the project and
 * add them to the project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AddUserGroupStep implements CalculationStepInterface
{
    /** @var ProjectItemCalculatorInterface */
    protected $contributorGroupCalculator;

    /**
     * @param ProjectItemCalculatorInterface $contributorGroupCalculator
     */
    public function __construct(ProjectItemCalculatorInterface $contributorGroupCalculator)
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
