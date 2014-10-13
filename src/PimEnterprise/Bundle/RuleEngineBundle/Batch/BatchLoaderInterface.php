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
use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Loads rules via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface BatchLoaderInterface extends LoaderInterface, StepExecutionAwareInterface
{
    /**
     * Load a rule known by the BatchLoaderInterface from the database.
     *
     * @return RuleInterface
     */
    public function loadFromDatabase();

    /**
     * Get the rule that will be loaded.
     *
     * @return string
     */
    public function getRuleCode();

    /**
     * Sets the rule needed to be loaded.
     *
     * @param string $code
     *
     * @return BatchLoaderInterface
     */
    public function setRuleCode($code);
}
