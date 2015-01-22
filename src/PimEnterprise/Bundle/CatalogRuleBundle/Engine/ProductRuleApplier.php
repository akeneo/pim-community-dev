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

use Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorFactoryInterface;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ObjectDetacherInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Component\Persistence\BulkSaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Akeneo\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Akeneo\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;

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

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var VersionManager */
    protected $versionManager;

    /** @var CacheClearer */
    protected $cacheClearer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $ruleDefinitionClass;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /**
     * @param ProductUpdaterInterface   $productUpdater
     * @param ValidatorInterface        $productValidator
     * @param BulkSaverInterface        $productSaver
     * @param EventDispatcherInterface  $eventDispatcher
     * @param ObjectDetacherInterface   $objectDetacher
     * @param VersionManager            $versionManager
     * @param CacheClearer              $cacheClearer
     * @param TranslatorInterface       $translator
     * @param PaginatorFactoryInterface $paginatorFactory
     * @param string                    $ruleDefinitionClass
     */
    public function __construct(
        ProductUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        BulkSaverInterface $productSaver,
        EventDispatcherInterface $eventDispatcher,
        ObjectDetacherInterface $objectDetacher,
        VersionManager $versionManager,
        CacheClearer $cacheClearer,
        TranslatorInterface $translator,
        PaginatorFactoryInterface $paginatorFactory,
        $ruleDefinitionClass
    ) {
        $this->productUpdater      = $productUpdater;
        $this->productValidator    = $productValidator;
        $this->productSaver        = $productSaver;
        $this->eventDispatcher     = $eventDispatcher;
        $this->objectDetacher      = $objectDetacher;
        $this->versionManager      = $versionManager;
        $this->cacheClearer        = $cacheClearer;
        $this->translator          = $translator;
        $this->paginatorFactory    = $paginatorFactory;
        $this->ruleDefinitionClass = $ruleDefinitionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $paginator = $this->paginatorFactory->createPaginator($subjectSet->getSubjectsCursor());
        $savingContext = $this->translator->trans(
            'pimee_catalog_rule.product.history',
            ['%rule%' => $rule->getCode()],
            null,
            'en'
        );

        $this->cacheClearer->addNonClearableEntity($this->ruleDefinitionClass);

        foreach ($paginator as $productsPage) {
            $this->updateProducts($productsPage, $rule->getActions());
            $this->validateProducts($productsPage, $subjectSet, $rule);
            $this->saveProducts($productsPage, $savingContext);

            $this->cacheClearer->clear();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

    /**
     * @param ProductInterface[] $products
     * @param ActionInterface[]  $actions
     */
    protected function updateProducts(array $products, $actions)
    {
        foreach ($actions as $action) {
            if ($action instanceof ProductSetValueActionInterface) {
                $this->applySetAction($products, $action);
            } elseif ($action instanceof ProductCopyValueActionInterface) {
                $this->applyCopyAction($products, $action);
            } else {
                throw new \LogicException(
                    sprintf('The action "%s" is not supported yet.', get_class($action))
                );
            }
        }
    }

    /**
     * @param ProductInterface[]      $products
     * @param RuleSubjectSetInterface $subjectSet
     * @param RuleInterface           $rule
     */
    protected function validateProducts(array $products, RuleSubjectSetInterface $subjectSet, RuleInterface $rule)
    {
        foreach ($products as $product) {
            $violations = $this->productValidator->validate($product);
            if ($violations->count() > 0) {
                $this->objectDetacher->detach($product);
                $reasons = [];
                foreach ($violations as $violation) {
                    $reasons[] = sprintf('%s : %s', $violation->getInvalidValue(), $violation->getMessage());
                }
                $this->eventDispatcher->dispatch(
                    RuleEvents::SKIPPED,
                    new SkippedSubjectRuleEvent($rule, $product, $reasons)
                );
            }
        }
    }

    /**
     * @param ProductInterface[] $products
     * @param string             $savingContext
     */
    protected function saveProducts(array $products, $savingContext)
    {
        $versioningState = $this->versionManager->isRealTimeVersioning();

        $this->versionManager->setContext($savingContext);
        $this->versionManager->setRealTimeVersioning(false);
        $this->productSaver->saveAll($products, ['recalculate' => false, 'schedule' => true]);
        $this->versionManager->setRealTimeVersioning($versioningState);
    }

    /**
     * Apply a copy action on a subject set.
     *
     * @param array                           $products
     * @param ProductCopyValueActionInterface $action
     *
     * @return ProductRuleApplier
     */
    protected function applyCopyAction(array $products, ProductCopyValueActionInterface $action)
    {
        $this->productUpdater->copyValue(
            $products,
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
     * @param ProductInterface[]             $products
     * @param ProductSetValueActionInterface $action
     *
     * @return ProductRuleApplier
     */
    protected function applySetAction(array $products, ProductSetValueActionInterface $action)
    {
        $this->productUpdater->setValue(
            $products,
            $action->getField(),
            $action->getValue(),
            $action->getLocale(),
            $action->getScope()
        );

        return $this;
    }
}
