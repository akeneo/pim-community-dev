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

/**
 * Batch rule step that allows to run a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleStep extends AbstractStep
{
    /** @var BatchLoaderInterface */
    protected $loader;

    /** @var BatchSelectorInterface */
    protected $selector;

    /** @var BatchApplierInterface */
    protected $applier;

    /**
     * {@inheritdoc]
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
     * {@inheritdoc]
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
     * {@inheritdoc]
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->loader->setStepExecution($stepExecution);
        $this->selector->setStepExecution($stepExecution);

        $rule = $this->loader->loadFromDatabase($this->getRuleCodeFromConfiguration());
        $loadedRule = $this->loader->load($rule);
        $subjects = $this->selector->select($loadedRule);

        $this->applier->apply($subjects);
    }

    /**
     * @return string
     */
    protected function getRuleCodeFromConfiguration()
    {
        return $this->getConfiguration()['ruleCode'];
    }

    /**
     * @return BatchLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param BatchLoaderInterface $loader
     *
     * @return RuleStep
     */
    public function setLoader(BatchLoaderInterface $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * @return BatchSelectorInterface
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @param BatchSelectorInterface $selector
     *
     * @return RuleStep
     */
    public function setSelector(BatchSelectorInterface $selector)
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * @return BatchApplierInterface
     */
    public function getApplier()
    {
        return $this->applier;
    }

    /**
     * @param BatchApplierInterface $applier
     *
     * @return RuleStep
     */
    public function setApplier(BatchApplierInterface $applier)
    {
        $this->applier = $applier;

        return $this;
    }

    /**
     * {@inheritdoc]
     */
    public function getConfigurableStepElements()
    {
        return [
            'loader' => $this->getLoader(),
        ];
    }
}
