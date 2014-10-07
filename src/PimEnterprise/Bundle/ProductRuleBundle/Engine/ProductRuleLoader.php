<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Engine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use PimEnterprise\Bundle\RuleEngineBundle\Batch\BatchLoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleDecorator;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleRepositoryInterface;

class ProductRuleLoader extends AbstractConfigurableStepElement implements BatchLoaderInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var RuleRepositoryInterface */
    protected $repository;

    /** @var string */
    protected $ruleCode;

    public function __construct(RuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function load(RuleInterface $rule)
    {
        //TODO: do not hardcode this
        $loaded = new LoadedRuleDecorator($rule);

        $content = json_decode($rule->getContent(), true);
        $loaded->setConditions($content['conditions']);

        return $loaded;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromDatabase()
    {
        return $this->repository->findOneBy(['code' => $this->ruleCode]);
    }


    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'ruleCode' => []
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleCode()
    {
        return $this->ruleCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleCode($ruleCode)
    {
        $this->ruleCode = $ruleCode;
    }
}
