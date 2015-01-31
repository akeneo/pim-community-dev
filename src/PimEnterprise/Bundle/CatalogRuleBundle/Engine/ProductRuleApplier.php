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

use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsSaver;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;

/**
 * Applies a rule on products
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{
    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var ProductsUpdater */
    protected $productsUpdater;

    /** @var ProductsValidator */
    protected $productsValidator;

    /** @var ProductsSaver */
    protected $productsSaver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CacheClearer */
    protected $cacheClearer;

    /** @var string */
    protected $ruleDefinitionClass;

    /**
     * @param PaginatorFactoryInterface $paginatorFactory
     * @param ProductsUpdater           $productsUpdater
     * @param ProductsValidator         $productsValidator
     * @param ProductsSaver             $productsSaver
     * @param EventDispatcherInterface  $eventDispatcher
     * @param CacheClearer              $cacheClearer
     * @param string                    $ruleDefinitionClass
     */
    public function __construct(
        PaginatorFactoryInterface $paginatorFactory,
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        CacheClearer $cacheClearer,
        $ruleDefinitionClass
    ) {
        $this->paginatorFactory    = $paginatorFactory;
        $this->productsUpdater     = $productsUpdater;
        $this->productsValidator   = $productsValidator;
        $this->productsSaver       = $productsSaver;
        $this->eventDispatcher     = $eventDispatcher;
        $this->cacheClearer        = $cacheClearer;
        $this->ruleDefinitionClass = $ruleDefinitionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $paginator = $this->paginatorFactory->createPaginator($subjectSet->getSubjectsCursor());

        $this->cacheClearer->addNonClearableEntity($this->ruleDefinitionClass);

        foreach ($paginator as $productsPage) {
            $this->productsUpdater->update($rule, $productsPage);
            $this->productsValidator->validate($rule, $productsPage);
            $this->productsSaver->save($rule, $productsPage);

            $this->cacheClearer->clear();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

}
