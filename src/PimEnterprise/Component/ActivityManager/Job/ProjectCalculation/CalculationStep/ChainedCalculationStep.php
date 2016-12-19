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

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ChainedCalculationStep implements CalculationStepInterface
{
    /** @var CalculationStepInterface[] */
    private $calculationSteps;

    /**
     * @param array $calculationSteps
     */
    public function __construct(array $calculationSteps)
    {
        $this->calculationSteps = $calculationSteps;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        foreach ($this->calculationSteps as $calculationStep) {
            $calculationStep->execute($product, $project);
        }
    }
}
