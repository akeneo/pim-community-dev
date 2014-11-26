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

use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Applies product rules via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{
    //TODO: Move those actions to a dedicated class
    const SET_ACTION  = 'set_value';
    const COPY_ACTION = 'copy_value';

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /**
     * @param ProductUpdaterInterface  $productUpdater
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ProductUpdaterInterface $productUpdater, EventDispatcherInterface $eventDispatcher)
    {
        $this->productUpdater  = $productUpdater;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(LoadedRuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $actions = $rule->getActions();
        foreach ($actions as $action) {
            if ($action instanceof ProductSetValueActionInterface) {
                $this->applySetAction($subjectSet, $action);
            } elseif ($action instanceof ProductCopyValueActionInterface) {
                $this->applyCopyAction($subjectSet, $action);
            } else {
                throw new \LogicException(
                    sprintf('The action "%s" is not supported yet.', get_class($action))
                );
            }
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(LoadedRuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }

    /**
     * Apply a copy action on a subhect set.
     *
     * @param RuleSubjectSetInterface         $subjectSet
     * @param ProductCopyValueActionInterface $action
     *
     * @return ProductRuleApplier
     */
    protected function applyCopyAction(RuleSubjectSetInterface $subjectSet, ProductCopyValueActionInterface $action)
    {
        $this->productUpdater->copyValue(
            $subjectSet->getSubjects(),
            $action->getFromField(),
            $action->getToField(),
            $action->getFromLocale(),
            $action->getToLocale(),
            $action->getFromScope(),
            $action->getToScope()
        );

        return $this;
    }

    /**
     * Applies a set action on a subject set.
     *
     * @param RuleSubjectSetInterface        $subjectSet
     * @param ProductSetValueActionInterface $action
     *
     * @return ProductRuleApplier
     */
    protected function applySetAction(RuleSubjectSetInterface $subjectSet, ProductSetValueActionInterface $action)
    {
        $this->productUpdater->setValue(
            $subjectSet->getSubjects(),
            $action->getField(),
            $action->getValue(),
            $action->getLocale(),
            $action->getScope()
        );

        return $this;
    }
}
