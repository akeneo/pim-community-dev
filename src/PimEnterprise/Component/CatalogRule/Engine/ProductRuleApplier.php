<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Engine;

use Akeneo\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver;
use PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsUpdater;
use PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var EntityManagerClearerInterface|null */
    protected $cacheClearer;

    /** @var int */
    protected $pageSize;

    /**
     * TODO @merge: on master remove PaginatorFactoryInterface from the constructor
     * TODO @merge: on master remove ObjectDetacherInterface from the constructor
     * TODO @merge: on master remove "= null" on ObjectManager $documentManager from the constructor
     *
     * @param PaginatorFactoryInterface          $paginatorFactory
     * @param ProductsUpdater                    $productsUpdater
     * @param ProductsValidator                  $productsValidator
     * @param ProductsSaver                      $productsSaver
     * @param EventDispatcherInterface           $eventDispatcher
     * @param ObjectDetacherInterface            $objectDetacher
     * @param EntityManagerClearerInterface|null $cacheClearer
     * @param int                                $pageSize
     */
    public function __construct(
        PaginatorFactoryInterface $paginatorFactory,
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        ObjectDetacherInterface $objectDetacher,
        EntityManagerClearerInterface $cacheClearer = null,
        $pageSize = 1000
    ) {
        $this->paginatorFactory = $paginatorFactory;
        $this->productsUpdater = $productsUpdater;
        $this->productsValidator = $productsValidator;
        $this->productsSaver = $productsSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->objectDetacher = $objectDetacher;
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

    /**
     * TODO @merge: on master rename and refactor method to only clear cache
     *
     * @param array $productsPage
     */
    protected function detachProducts(array $productsPage = [])
    {
        if (null === $this->cacheClearer) {
            foreach ($productsPage as $product) {
                $this->objectDetacher->detach($product);
            }
        } else {
            $this->cacheClearer->clear();
        }
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
        $this->detachProducts($products);
    }
}
