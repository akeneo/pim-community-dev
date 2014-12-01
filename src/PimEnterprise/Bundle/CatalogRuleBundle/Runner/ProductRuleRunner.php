<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Runner;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\AbstractRunner;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product rule runner
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleRunner extends AbstractRunner
{
    /** @var string */
    protected $productConditionClass;

    /**
     * @param LoaderInterface   $loader
     * @param SelectorInterface $selector
     * @param ApplierInterface  $applier
     * @param string            $productConditionClass
     */
    public function __construct(
        LoaderInterface $loader,
        SelectorInterface $selector,
        ApplierInterface $applier,
        $productConditionClass
    ) {
        parent::__construct($loader, $selector, $applier);

        $refClass = new \ReflectionClass($productConditionClass);
        $interface = 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface';
        if (!$refClass->implementsInterface($interface)) {
            throw new \InvalidArgumentException(
                sprintf('The provided class name "%s" must implement interface "%s".', $productConditionClass, $interface)
            );
        }

        $this->productConditionClass = $productConditionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function run(RuleDefinitionInterface $definition, array $options = [])
    {
        $options = $this->resolveOptions($options);
        $definition = $this->loadRule($definition, $options);

        $subjects = $this->selector->select($definition);
        if (!empty($subjects)) {
            $this->applier->apply($definition, $subjects);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleDefinitionInterface $definition)
    {
        return 'product' === $definition->getType();
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['selected_products' => []]);
        $resolver->setAllowedTypes(['selected_products' => 'array']);
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @param RuleDefinitionInterface $definition
     * @param array                   $options
     *
     * @return \PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface
     */
    protected function loadRule(RuleDefinitionInterface $definition, array $options)
    {
        $definition = $this->loader->load($definition);
        if (!empty($options['selected_products'])) {
            /** @var ProductConditionInterface $condition */
            $condition = new $this->productConditionClass([
                    'field' => 'id',
                    'operator' => 'IN',
                    'value' => $options['selected_products']
                ]);
            $definition->addCondition($condition);
        }

        return $definition;
    }
}
