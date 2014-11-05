<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Batch;

use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Get a rule from database with the given rule code
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface RuleReaderInterface extends StepExecutionAwareInterface
{
    /**
     * Get the Rule from database
     *
     * @return \PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface
     */
    public function read();
}
