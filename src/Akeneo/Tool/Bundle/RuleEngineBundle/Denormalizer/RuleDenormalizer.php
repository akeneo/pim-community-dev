<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Denormalizer;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleDenormalizer implements DenormalizerInterface, ChainedDenormalizerAwareInterface
{
    /** @var DenormalizerInterface */
    protected $chainedDenormalizer;

    /** @var string */
    protected $ruleClass;

    /** @var string */
    protected $definitionClass;

    /** @var string */
    protected $type;

    /**
     * @param string $ruleClass
     * @param string $definitionClass
     * @param string $type
     */
    public function __construct($ruleClass, $definitionClass, $type)
    {
        $this->ruleClass = $ruleClass;
        $this->definitionClass = $definitionClass;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     *
     * @return RuleDefinitionInterface
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $this->checkRuleKeys($data);

        $rule = $this->getRuleDefinition($context);
        $rule->setCode($data['code']);
        $rule->setType($this->type);

        if (isset($data['priority'])) {
            $rule->setPriority((int) $data['priority']);
        }

        if (isset($data['enabled'])) {
            $rule->setEnabled($data['enabled']);
        }

        $rawContent = ['conditions' => $data['conditions'], 'actions' => $data['actions']];
        $rule->setContent($rawContent);

        $content = $this->chainedDenormalizer->denormalize($rawContent, $class, 'rule_content', $context);

        foreach ($content['conditions'] as $condition) {
            $rule->addCondition($condition);
        }
        foreach ($content['actions'] as $action) {
            $rule->addAction($action);
        }

        if (isset($data['labels'])) {
            foreach ($data['labels'] as $locale => $label) {
                $rule->setLabel($locale, $label);
            }
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $this->ruleClass === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function setChainedDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->chainedDenormalizer = $denormalizer;
    }

    /**
     * @param array $context
     *
     * @return RuleDefinitionInterface
     */
    protected function getRuleDefinition(array $context)
    {
        if (isset($context['object'])) {
            return $context['object'];
        }

        if (isset($context['definitionObject'])) {
            $definition = $context['definitionObject'];
        } else {
            $definition = new $this->definitionClass();
        }

        return new $this->ruleClass($definition);
    }

    /**
     * Checks if the rule have a 'conditions' and 'actions' keys
     *
     * @param array $data
     *
     * @throws \LogicException
     */
    protected function checkRuleKeys(array $data)
    {
        if (!array_key_exists('conditions', $data)) {
            throw new \LogicException(sprintf('Rule content "%s" should have a "conditions" key.', $data['code']));
        }

        if (!array_key_exists('actions', $data)) {
            throw new \LogicException(sprintf('Rule content "%s" should have a "actions" key.', $data['code']));
        }
    }
}
