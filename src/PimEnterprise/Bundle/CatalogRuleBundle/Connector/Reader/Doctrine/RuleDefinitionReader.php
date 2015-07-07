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
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;

/**
 * Get rules definition
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleDefinitionReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool Checks if all rules are sent to the processor */
    protected $allRulesRead = false;

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
        if ($this->allRulesRead) {
            return null;
        }

        $this->allRulesRead = true;

        return $this->ruleRepository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
