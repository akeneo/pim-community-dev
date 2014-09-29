<?php

namespace Pim\Bundle\RulesEngineBundle\Runner;

class ChainedRunner implements RunnerInterface
{
    /** RunnerInterface[] ordered runner with priority */
    protected $runners;

    public function registerRunner(RunnerInterface $runner)
    {
        $this->runners[]= $runner;
    }

    public function supports(RuleInterface $rule)
    {
        return true;
    }

    public function run(RuleInterface $rule)
    {
        foreach ($runners as $runner) {
            if ($runner->supports($rule)) {
                return $runner->run($rule);
            }
        }

        throw new \LogicException('No runner available');
    }
}
