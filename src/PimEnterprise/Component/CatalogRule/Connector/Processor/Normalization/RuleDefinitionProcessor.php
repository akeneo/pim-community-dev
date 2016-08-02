<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
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
