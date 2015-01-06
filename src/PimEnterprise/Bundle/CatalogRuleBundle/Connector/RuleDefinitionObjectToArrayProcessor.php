<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use Pim\Bundle\BaseConnectorBundle\Processor\DummyProcessor;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Processes and transform rules definition
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleDefinitionObjectToArrayProcessor extends DummyProcessor
{
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
        $rules = [];
        foreach ($item as $ruleDefinition) {
            $normalizedRule = $this->ruleNormalizer->normalize($ruleDefinition);

            $rules[$ruleDefinition->getCode()]['priority']   = $ruleDefinition->getPriority();
            $rules[$ruleDefinition->getCode()]['conditions'] = $normalizedRule['conditions'];
            $rules[$ruleDefinition->getCode()]['actions']    = $normalizedRule['actions'];
        }

        return ['rules' => $rules];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
