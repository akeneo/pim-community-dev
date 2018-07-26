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

use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsSaver;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
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

    /**
     * @param PaginatorFactoryInterface $paginatorFactory
     * @param ProductsUpdater           $productsUpdater
     * @param ProductsValidator         $productsValidator
     * @param ProductsSaver             $productsSaver
     * @param EventDispatcherInterface  $eventDispatcher
     * @param ObjectDetacherInterface   $objectDetacher
     */
    public function __construct(
        PaginatorFactoryInterface $paginatorFactory,
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->paginatorFactory = $paginatorFactory;
        $this->productsUpdater = $productsUpdater;
        $this->productsValidator = $productsValidator;
        $this->productsSaver = $productsSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $paginator = $this->paginatorFactory->createPaginator($subjectSet->getSubjectsCursor());

        foreach ($paginator as $productsPage) {
            $this->productsUpdater->update($rule, $productsPage);
            $validProducts = $this->productsValidator->validate($rule, $productsPage);
            $this->productsSaver->save($rule, $validProducts);
            $this->detachProducts($productsPage);
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

    /**
     * @param array $productsPage
     */
    protected function detachProducts(array $productsPage)
    {
        foreach ($productsPage as $product) {
            $this->objectDetacher->detach($product);
        }
    }
}
