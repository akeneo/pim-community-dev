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

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Resource\Model\BulkSaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Applies product rules via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var VersionManager */
    protected $versionManager;

    /**
     * @param ProductUpdaterInterface  $productUpdater
     * @param ValidatorInterface       $productValidator
     * @param BulkSaverInterface       $productSaver
     * @param EventDispatcherInterface $eventDispatcher
     * @param ObjectManager            $objectManager
     * @param VersionManager           $versionManager
     */
    public function __construct(
        ProductUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        BulkSaverInterface $productSaver,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $objectManager,
        VersionManager $versionManager
    )
    {
        $this->productUpdater   = $productUpdater;
        $this->productValidator = $productValidator;
        $this->productSaver     = $productSaver;
        $this->eventDispatcher  = $eventDispatcher;
        $this->objectManager    = $objectManager;
        $this->versionManager   = $versionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $this->updateProducts($subjectSet, $rule->getActions());
        $this->validateProducts($subjectSet);
        $this->saveProducts($subjectSet, sprintf('Applied rule "%s"', $rule->getCode()));

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

    /**
     * @param RuleSubjectSetInterface                                        $subjectSet
     * @param \PimEnterprise\Bundle\RuleEngineBundle\Model\ActionInterface[] $actions
     */
    protected function updateProducts(RuleSubjectSetInterface $subjectSet, $actions)
    {
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
    }

    /**
     * @param RuleSubjectSetInterface $subjectSet
     */
    protected function validateProducts(RuleSubjectSetInterface $subjectSet)
    {
        foreach ($subjectSet->getSubjects() as $product) {
            $violations = $this->productValidator->validate($product);
            if ($violations->count() > 0) {
                $this->objectManager->detach($product);
                $reasons = [];
                /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
                foreach ($violations as $violation) {
                    $reasons[] = sprintf('%s : %s', $violation->getInvalidValue(), $violation->getMessage());
                }
                $subjectSet->skipSubject($product, $reasons);
            }
        }
    }

    /**
     * @param RuleSubjectSetInterface $subjectSet
     * @param string                  $savingContext
     */
    protected function saveProducts(RuleSubjectSetInterface $subjectSet, $savingContext)
    {
        $this->versionManager->setContext($savingContext);
        $this->productSaver->saveAll($subjectSet->getSubjects(), ['recalculate' => false, 'schedule' => true]);
    }

    /**
     * Apply a copy action on a subject set.
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
