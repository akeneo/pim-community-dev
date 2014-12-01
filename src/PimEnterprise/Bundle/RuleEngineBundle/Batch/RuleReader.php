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

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;

/**
 * Get a rule from database with the given rule code
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleReader extends AbstractConfigurableStepElement implements RuleReaderInterface
{
    /** @var string */
    protected $ruleCode;

    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param RuleDefinitionRepositoryInterface $ruleRepository
     */
    public function __construct(RuleDefinitionRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->ruleRepository->findBy(['code' => $this->getRuleCode()]);
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
