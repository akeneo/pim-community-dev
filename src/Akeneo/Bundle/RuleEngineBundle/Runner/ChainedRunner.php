<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Runner;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Chained rule runner. Find the runner able to handle a rule, and run it.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChainedRunner implements DryRunnerInterface
{
    /** @var RunnerInterface[] ordered runner with priority */
    protected $runners = [];

    /**
     * @param RunnerInterface $runner
     *
     * @return ChainedRunner
     */
    public function addRunner(RunnerInterface $runner)
    {
        $this->runners[] = $runner;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleDefinitionInterface $definition)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function run(RuleDefinitionInterface $definition, array $options = [])
    {
        foreach ($this->runners as $runner) {
            if ($runner->supports($definition)) {
                return $runner->run($definition);
            }
        }

        throw new \LogicException(sprintf('No runner available for the rule "%s".', $definition->getCode()));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function dryRun(RuleDefinitionInterface $definition, array $options = [])
    {
        foreach ($this->runners as $runner) {
            if ($runner instanceof DryRunnerInterface && $runner->supports($definition)) {
                return $runner->dryRun($definition);
            }
        }

        throw new \LogicException(sprintf('No dry runner available for the rule "%s".', $definition->getCode()));
    }
}
