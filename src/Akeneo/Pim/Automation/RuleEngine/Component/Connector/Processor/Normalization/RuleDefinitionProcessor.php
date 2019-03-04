<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Normalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Processes and transforms rules definition to array of rules
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleDefinitionProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var NormalizerInterface */
    protected $ruleNormalizer;

    /**
     * @param NormalizerInterface $ruleNormalizer
     */
    public function __construct(NormalizerInterface $ruleNormalizer)
    {
        $this->ruleNormalizer = $ruleNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $normalizedRule = $this->ruleNormalizer->normalize($item);

        unset($normalizedRule['code']);
        unset($normalizedRule['type']);

        if (isset($normalizedRule['conditions'])) {
            $sortedConditions = [];
            foreach ($normalizedRule['conditions'] as $condition) {
                ksort($condition);
                $sortedConditions[] = $condition;
            }
            $normalizedRule['conditions'] = $sortedConditions;
        }

        if (isset($normalizedRule['actions'])) {
            $sortedActions = [];
            foreach ($normalizedRule['actions'] as $action) {
                ksort($action);
                $sortedActions[] = $action;
            }
            $normalizedRule['actions'] = $sortedActions;
        }

        $rule[$item->getCode()] = $normalizedRule;

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
