<?php

namespace PimEnterprise\Bundle\RuleEngineBundle\Batch;

use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

interface BatchLoaderInterface extends LoaderInterface, StepExecutionAwareInterface
{
    /**
     * @return RuleInterface
     */
    public function loadFromDatabase();

    /**
     * @return string
     */
    public function getRuleCode();

    /**
     * @param string $code
     *
     * @return BatchLoaderInterface
     */
    public function setRuleCode($code);
}
