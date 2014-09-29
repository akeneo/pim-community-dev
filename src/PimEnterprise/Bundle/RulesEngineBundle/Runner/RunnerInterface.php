<?php

namespace Pim\Bundle\RulesEngineBundle\Runner;

use Pim\Bundle\RulesEngineBundle\Model\RuleInterface;

interface RunnerInterface
{
    public function run(RuleInterface $rule);

    public function supports(RuleInterface $rule);
}
