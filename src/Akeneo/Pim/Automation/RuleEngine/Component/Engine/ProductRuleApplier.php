<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsSaver;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Applies a rule on products
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{

    /** @var ProductsUpdater */
    protected $productsUpdater;

    /** @var ProductsValidator */
    protected $productsValidator;

    /** @var ProductsSaver */
    protected $productsSaver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;


    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    /** @var int */
    protected $pageSize;

    /**
     * @param ProductsUpdater $productsUpdater
     * @param ProductsValidator $productsValidator
     * @param ProductsSaver $productsSaver
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerClearerInterface $cacheClearer
     * @param int $pageSize
     */
    public function __construct(
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerClearerInterface $cacheClearer,
        $pageSize = 1000
    ) {
        $this->productsUpdater = $productsUpdater;
        $this->productsValidator = $productsValidator;
        $this->productsSaver = $productsSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheClearer = $cacheClearer;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $productsPage = [];
        foreach ($subjectSet->getSubjectsCursor() as $product) {
            $productsPage[] = $product;
            if (count($productsPage) >= $this->pageSize) {
                $this->updateProducts($rule, $productsPage);
                $productsPage = [];
            }
        }

        if (count($productsPage) > 0) {
            $this->updateProducts($rule, $productsPage);
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

    protected function clearCache()
    {
        $this->cacheClearer->clear();
    }

    /**
     * @param RuleInterface $rule
     * @param array $products
     */
    protected function updateProducts(RuleInterface $rule, array $products)
    {
        $this->productsUpdater->update($rule, $products);
        $validProducts = $this->productsValidator->validate($rule, $products);
        $this->productsSaver->save($rule, $validProducts);
        $this->clearCache();
    }
}
