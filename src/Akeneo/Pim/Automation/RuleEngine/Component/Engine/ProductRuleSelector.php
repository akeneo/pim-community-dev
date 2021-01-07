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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * Selects subjects impacted by a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleSelector implements SelectorInterface
{
    /** @var string */
    protected $subjectSetClass;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $queryBuilderFactory;

    /** @var ProductRepositoryInterface */
    protected $repo;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ProductQueryBuilderFactoryInterface $queryBuilderFactory
     * @param ProductRepositoryInterface          $repo
     * @param EventDispatcherInterface            $eventDispatcher
     * @param string                              $subjectSetClass     should implement RuleSubjectSetInterface
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $queryBuilderFactory,
        ProductRepositoryInterface $repo,
        EventDispatcherInterface $eventDispatcher,
        $subjectSetClass
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->repo = $repo;
        $this->eventDispatcher = $eventDispatcher;
        $this->subjectSetClass = $subjectSetClass;
    }

    /**
     * {@inheritdoc}
     */
    public function select(RuleInterface $rule): RuleSubjectSetInterface
    {
        $this->eventDispatcher->dispatch(new RuleEvent($rule), RuleEvents::PRE_SELECT);

        $subjectSet = new $this->subjectSetClass();
        $pqb = $this->queryBuilderFactory->create([
            'with_document_type_facet' => true
        ]);

        foreach ($rule->getConditions() as $condition) {
            Assert::implementsInterface($condition, ProductConditionInterface::class);
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

        $this->eventDispatcher->dispatch(new SelectedRuleEvent($rule, $subjectSet), RuleEvents::POST_SELECT);

        return $subjectSet;
    }
}
