<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Runner;

use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonRunnableException;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product rule runner
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleRunner implements DryRunnerInterface
{
    /** @var BuilderInterface */
    protected $builder;

    /** @var SelectorInterface */
    protected $selector;

    /** @var ApplierInterface */
    protected $applier;

    /** @var string */
    protected $productCondClass;

    /**
     * @param BuilderInterface  $builder
     * @param SelectorInterface $selector
     * @param ApplierInterface  $applier
     * @param string            $productCondClass should implement ProductConditionInterface
     */
    public function __construct(
        BuilderInterface $builder,
        SelectorInterface $selector,
        ApplierInterface $applier,
        $productCondClass
    ) {
        $this->builder = $builder;
        $this->selector = $selector;
        $this->applier = $applier;
        $this->productCondClass = $productCondClass;
    }

    /**
     * {@inheritdoc}
     */
    public function run(RuleDefinitionInterface $definition, array $options = [])
    {
        $options = $this->resolveOptions($options);
        $rule = $this->loadRule($definition, $options);
        $this->checkRuleIsEnabled($rule);

        $subjectSet = $this->selector->select($rule);
        if (!empty($subjectSet)) {
            $this->applier->apply($rule, $subjectSet);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dryRun(RuleDefinitionInterface $definition, array $options = []): RuleSubjectSetInterface
    {
        $options = $this->resolveOptions($options);
        $rule = $this->loadRule($definition, $options);

        return $this->selector->select($rule);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleDefinitionInterface $definition): bool
    {
        return 'product' === $definition->getType();
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['selected_entities_with_values' => [], 'username' => null]);
        $resolver->setAllowedTypes('selected_entities_with_values', 'array');
        $resolver->setAllowedTypes('username', ['string', 'null']);
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @param RuleDefinitionInterface $definition
     * @param array                   $options
     *
     * @return RuleInterface
     */
    protected function loadRule(RuleDefinitionInterface $definition, array $options): RuleInterface
    {
        $rule = $this->builder->build($definition);

        if (!empty($options['selected_entities_with_values'])) {
            $condition = new $this->productCondClass(
                [
                    'field'    => 'id',
                    'operator' => 'IN',
                    'value'    => $options['selected_entities_with_values'],
                ]
            );
            $rule->addCondition($condition);
        }

        return $rule;
    }

    protected function checkRuleIsEnabled(RuleInterface $rule): void
    {
        if (!$rule->isEnabled()) {
            throw new NonRunnableException(
                sprintf('The "%s" rule is disabled.', $rule->getCode())
            );
        }
    }
}
