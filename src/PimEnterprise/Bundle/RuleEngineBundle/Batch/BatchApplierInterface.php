<?php

namespace PimEnterprise\Bundle\RuleEngineBundle\Batch;

use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;

interface BatchApplierInterface extends ApplierInterface, StepExecutionAwareInterface
{
}
