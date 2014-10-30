<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Engine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactory;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
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

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /**
     * @param string                     $subjectSetClass
     * @param ProductQueryFactory        $productQueryFactory
     * @param ProductRepositoryInterface $repo
     * @param EventDispatcher            $eventDispatcher
     */
    public function __construct(
        $subjectSetClass,
        ProductQueryFactory $productQueryFactory,
        ProductRepositoryInterface $repo,
        EventDispatcher $eventDispatcher
    ) {
        $this->subjectSetClass     = $subjectSetClass;
        $this->productQueryFactory = $productQueryFactory;
        $this->repo                = $repo;
        $this->eventDispatcher     = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function select(RuleInterface $rule)
    {
        $resolver = new OptionsResolver();
        $this->configureCondition($resolver);

        /** @var RuleSubjectSetInterface $subjectSet */
        $subjectSet = new $this->subjectSetClass();

        $this->eventDispatcher->dispatch(RuleEvents::PRE_SELECT, new RuleEvent($rule));

        $pqb = $this->productQueryFactory->create();

        $conditions = $rule->getConditions();

        foreach ($conditions as $condition) {
            $condition = $resolver->resolve($condition);

            $pqb->addFilter($condition['field'], $condition['operator'], $condition['value']);
        }

        $products = $pqb->getQueryBuilder()->getQuery()->execute();

        $subjectSet->setCode($rule->getCode());
        $subjectSet->setType('product');
        $subjectSet->setSubjects($products);

        $this->eventDispatcher->dispatch(RuleEvents::POST_SELECT, new SelectedRuleEvent($rule, $subjectSet));

        return $subjectSet;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule)
    {
        return 'product' === $rule->getType() &&
            $rule instanceof LoadedRuleInterface;
    }

    /**
     * Configure the condition's optionResolver
     * @param  OptionsResolver $optionsResolver
     */
    protected function configureCondition(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['field', 'operator', 'value']);
    }
}
