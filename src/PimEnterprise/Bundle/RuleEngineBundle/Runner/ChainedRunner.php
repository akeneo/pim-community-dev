<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Runner;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Chained rule runner
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
    public function supports(RuleSubjectSetInterface $rule)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function run(RuleSubjectSetInterface $subjectSet)
    {
        foreach ($this->runners as $runner) {
            if ($runner->supports($subjectSet)) {
                return $runner->run($subjectSet);
            }
        }

        throw new \LogicException(sprintf('No runner available for the rule "%s".', $runnable->getCode()));
    }

    /**
     * {@inheritdoc}
     */
    public function dryRun(RuleSubjectSetInterface $subjectSet)
    {
        foreach ($this->runners as $runner) {
            if ($runner instanceof DryRunnerInterface && $runner->supports($subjectSet)) {
                return $runner->dryRun($subjectSet);
            }
        }

        throw new \LogicException(sprintf('No dry runner available for the rule "%s".', $subjectSet->getCode()));
    }
}
