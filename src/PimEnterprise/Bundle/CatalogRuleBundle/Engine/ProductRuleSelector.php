<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactory;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Selects subjects impacted by a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleSelector implements SelectorInterface
{
    /** @var string */
    protected $subjectSetClass;

    /** @var ProductQueryFactory */
    protected $productQueryFactory;

    /** @var ProductRepositoryInterface */
    protected $repo;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ProductQueryFactoryInterface $productQueryFactory
     * @param ProductRepositoryInterface   $repo
     * @param EventDispatcherInterface     $eventDispatcher
     * @param string                       $subjectSetClass
     */
    public function __construct(
        ProductQueryFactoryInterface $productQueryFactory,
        ProductRepositoryInterface $repo,
        EventDispatcherInterface $eventDispatcher,
        $subjectSetClass
    ) {
        $this->productQueryFactory = $productQueryFactory;
        $this->repo                = $repo;
        $this->eventDispatcher     = $eventDispatcher;
        $this->subjectSetClass     = $subjectSetClass;

        $refClass = new \ReflectionClass($subjectSetClass);
        $interface = 'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface';
        if (!$refClass->implementsInterface($interface)) {
            throw new \InvalidArgumentException(
                sprintf('The provided class name "%s" must implement interface "%s".', $subjectSetClass, $interface)
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public function select(LoadedRuleInterface $rule)
    {
        $resolver = new OptionsResolver();
        $this->configureCondition($resolver);

        $this->eventDispatcher->dispatch(RuleEvents::PRE_SELECT, new RuleEvent($rule));

        /** @var RuleSubjectSetInterface $subjectSet */
        $subjectSet = new $this->subjectSetClass();
        $conditions = $rule->getConditions();
        $pqb = $this->productQueryFactory->create();

        foreach ($conditions as $condition) {
            $condition = $resolver->resolve($condition);
            $pqb->addFilter($condition['field'], $condition['operator'], $condition['value']);
        }

        $products = $pqb->execute();

        $subjectSet->setCode($rule->getCode());
        $subjectSet->setType('product');
        $subjectSet->setSubjects($products);

        $this->eventDispatcher->dispatch(RuleEvents::POST_SELECT, new SelectedRuleEvent($rule, $subjectSet));

        return $subjectSet;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(LoadedRuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }

    /**
     * Configure the condition's optionResolver
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureCondition(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['field', 'operator', 'value']);
    }
}
