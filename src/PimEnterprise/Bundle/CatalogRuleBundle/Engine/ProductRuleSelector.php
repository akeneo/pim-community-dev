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
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @param string                       $subjectSetClass should implement \PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface
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
    }

    /**
     * {@inheritdoc}
     */
    public function select(RuleInterface $rule)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_SELECT, new RuleEvent($rule));

        /** @var RuleSubjectSetInterface $subjectSet */
        $subjectSet = new $this->subjectSetClass();
        $pqb = $this->productQueryFactory->create();

        /** @var \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface $condition */
        foreach ($rule->getConditions() as $condition) {
            // TODO : we need to pass the locale and scope as a context here !
            $pqb->addFilter($condition->getField(), $condition->getOperator(), $condition->getValue());
        }

        $products = $pqb->execute();

        $subjectSet->setCode($rule->getCode());
        $subjectSet->setType('product');
        $subjectSet->setSubjects($products);

        $this->eventDispatcher->dispatch(RuleEvents::POST_SELECT, new SelectedRuleEvent($rule, $subjectSet));

        return $subjectSet;
    }
}
