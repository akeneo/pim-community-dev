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

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface;

/**
 * Batch rule step that allows to run a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleStep extends AbstractStep
{
    /** @var RuleReaderInterface */
    protected $reader;

    /** @var RunnerInterface */
    protected $runner;

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration = array();
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                foreach ($stepElement->getConfiguration() as $key => $value) {
                    if (!isset($configuration[$key]) || $value) {
                        $configuration[$key] = $value;
                    }
                }
            }
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                $stepElement->setConfiguration($config);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->reader->setStepExecution($stepExecution);

        $rule = $this->reader->read();

        $this->runner->run($rule);
    }

    /**
     * @return RuleReaderInterface
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param RuleReaderInterface $reader
     *
     * @return RuleStep
     */
    public function setReader(RuleReaderInterface $reader)
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @return RunnerInterface
     */
    public function getRunner()
    {
        return $this->runner;
    }

    /**
     * @param RunnerInterface $runner
     *
     * @return RuleStep
     */
    public function setRunner(RunnerInterface $runner)
    {
        $this->runner = $runner;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableStepElements()
    {
        return [
            'reader' => $this->getReader(),
        ];
    }
}
