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

use Pim\Bundle\CatalogBundle\Query\ProductQueryFactory;
use Pim\Bundle\CatalogBundle\Query\ProductQueryFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
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
     * @param string                       $subjectSetClass should implement
     *                                     \Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface
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

        $subjectSet = new $this->subjectSetClass();
        $pqb = $this->productQueryFactory->create();

        foreach ($rule->getConditions() as $condition) {
            $pqb->addFilter(
                $condition->getField(),
                $condition->getOperator(),
                $condition->getValue(),
                ['locale' => $condition->getLocale(), 'scope' => $condition->getScope()]
            );
        }

        $productsCursor = $pqb->execute();
        $subjectSet->setCode($rule->getCode());
        $subjectSet->setType('product');
        $subjectSet->setSubjectsCursor($productsCursor);

        $this->eventDispatcher->dispatch(RuleEvents::POST_SELECT, new SelectedRuleEvent($rule, $subjectSet));

        return $subjectSet;
    }
}
