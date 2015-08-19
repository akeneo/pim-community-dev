<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Connector\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;

/**
 * Get rules definition
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleDefinitionReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool Checks if all rules are sent to the processor */
    protected $isExecuted = false;

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
        if ($this->isExecuted) {
            return null;
        }

        $results = $this->getResults();

        $this->isExecuted = true;

        foreach ($results as $result) {
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return \ArrayIterator
     */
    protected function getResults()
    {
        return $this->ruleRepository->findAll();
    }
}
