<?php

namespace PimEnterprise\Bundle\RuleEngineBundle\Batch;

use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;

interface BatchSelectorInterface extends SelectorInterface, StepExecutionAwareInterface
{
}
