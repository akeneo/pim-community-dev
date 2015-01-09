<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorInterface;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ObjectDetacherInterface;
use Akeneo\Component\Persistence\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorFactoryInterface;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface;

class ProductRuleApplierSpec extends ObjectBehavior
{
    const RULE_DEFINITION_CLASS = 'Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition';

    function let(
        EventDispatcherInterface $eventDispatcher,
        ProductUpdaterInterface $productUpdater,
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        ValidatorInterface $productValidator,
        ObjectDetacherInterface $objectDetacher,
        CacheClearer $cacheClearer,
        PaginatorFactoryInterface $paginatorFactory,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith(
            $productUpdater,
            $productValidator,
            $productSaver,
            $eventDispatcher,
            $objectDetacher,
            $versionManager,
            $cacheClearer,
            $translator,
            $paginatorFactory,
            self::RULE_DEFINITION_CLASS
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier');
    }

    function it_is_a_rule_applier()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Engine\ApplierInterface');
    }

    function it_applies_a_rule_which_does_not_update_products(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        CursorInterface $cursor,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        // update products
        $rule->getActions()->willReturn([]);

        $paginator->valid()->shouldBeCalled()->willReturn(false);
        $paginator->rewind()->shouldBeCalled()->willReturn(null);
        $paginatorFactory->createPaginator($cursor)->shouldBeCalled()->willReturn($paginator);
        $subjectSet->getSubjectsCursor()->shouldBeCalled()->willReturn($cursor);

        $productUpdater->setValue(Argument::any())->shouldNotBeCalled();
        $productUpdater->copyValue(Argument::any())->shouldNotBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate(Argument::any())->shouldNotBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_which_has_a_set_action(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        $productSaver,
        $versionManager,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductSetValueActionInterface $action,
        ProductInterface $selectedProduct,
        ConstraintViolationList $violationList,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator,
        CursorInterface $cursor,
        $translator,
        $cacheClearer
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        // update products
        $action->getField()->willReturn('sku');
        $action->getValue()->willReturn('foo');
        $action->getScope()->willReturn('ecommerce');
        $action->getLocale()->willReturn('en_US');
        $rule->getActions()->willReturn([$action]);

        // paginator mocking
        $productArray = [];
        for ($i = 0; $i < 13; $i++) {
            $productArray[] = $selectedProduct;
        }
        $indexPage = 0;
        $paginator->current()->willReturn(array_slice($productArray, $indexPage * 10, 10));
        $paginator->next()->shouldBeCalled()->will(function () use ($paginator, &$productArray, &$indexPage) {
            $paginator->current()->willReturn(array_slice($productArray, $indexPage * 10, 10));
            $indexPage++;
        });
        $paginator->rewind()->shouldBeCalled()->will(function () use (&$indexPage) {
            $indexPage = 0;
        });
        $paginator->valid()->shouldBeCalled()->will(function () use (&$indexPage) {
            return $indexPage < 3;
        });

        $paginatorFactory->createPaginator($cursor)->shouldBeCalled()->willReturn($paginator);
        $subjectSet->getSubjectsCursor()->shouldBeCalled()->willReturn($cursor);

        $productUpdater->setValue(Argument::any(), 'sku', 'foo', 'en_US', 'ecommerce')->shouldBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate(Argument::any())->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);

        $translator->trans(Argument::cetera())->willReturn('Applied rule "rule_one"');

        // save products
        $versionManager->isRealTimeVersioning()->willReturn(false);
        $versionManager->setContext('Applied rule "rule_one"')->shouldBeCalled();
        $versionManager->setRealTimeVersioning(false)->shouldBeCalled();
        $productSaver->saveAll(Argument::any(), ['recalculate' => false, 'schedule' => true])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
    }

    function it_applies_a_rule_which_has_a_copy_action(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        $productSaver,
        $versionManager,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductCopyValueAction $action,
        ProductInterface $selectedProduct,
        ConstraintViolationList $violationList,
        $cacheClearer,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator,
        CursorInterface $cursor,
        $translator
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        // update products
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('description');
        $action->getFromLocale()->willReturn('fr_FR');
        $action->getToLocale()->willReturn('fr_CH');
        $action->getFromScope()->willReturn('ecommerce');
        $action->getToScope()->willReturn('tablet');
        $rule->getActions()->willReturn([$action]);

        // paginator mocking
        $productArray = [$selectedProduct];
        $indexPage = 0;
        $paginator->current()->willReturn(array_slice($productArray, $indexPage * 10, 10));
        $paginator->next()->shouldBeCalled()->will(function () use ($paginator, &$productArray, &$indexPage) {
            $paginator->current()->willReturn(array_slice($productArray, $indexPage * 10, 10));
            $indexPage++;
        });
        $paginator->rewind()->shouldBeCalled()->will(function () use (&$indexPage) {
            $indexPage = 0;
        });
        $paginator->valid()->shouldBeCalled()->will(function () use (&$indexPage) {
            return $indexPage < 2;
        });
        $paginatorFactory->createPaginator($cursor)->shouldBeCalled()->willReturn($paginator);
        $subjectSet->getSubjectsCursor()->shouldBeCalled()->willReturn($cursor);

        $productUpdater
            ->copyValue([$selectedProduct], 'sku', 'description', 'fr_FR', 'fr_CH', 'ecommerce', 'tablet')
            ->shouldBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate($selectedProduct)->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);

        $translator->trans(Argument::cetera())->willReturn('Applied rule "rule_one"');

        // save products
        $versionManager->isRealTimeVersioning()->willReturn(false);
        $versionManager->setContext('Applied rule "rule_one"')->shouldBeCalled();
        $versionManager->setRealTimeVersioning(false)->shouldBeCalled();
        $productSaver->saveAll([$selectedProduct], ['recalculate' => false, 'schedule' => true])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
    }

    function it_applies_a_rule_with_invalid_product(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        $objectDetacher,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductCopyValueAction $action,
        ProductInterface $validProduct,
        ProductInterface $invalidProduct,
        ConstraintViolationList $emptyViolationList,
        ConstraintViolationList $notEmptyViolationList,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator,
        CursorInterface $cursor,
        $cacheClearer
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        // update products
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('description');
        $action->getFromLocale()->willReturn('fr_FR');
        $action->getToLocale()->willReturn('fr_CH');
        $action->getFromScope()->willReturn('ecommerce');
        $action->getToScope()->willReturn('tablet');
        $rule->getActions()->willReturn([$action]);

        // paginator mocking
        $productArray = [$validProduct, $invalidProduct];
        $indexPage = 0;
        $paginator->current()->willReturn(array_slice($productArray, $indexPage * 10, 10));
        $paginator->next()->shouldBeCalled()->will(function () use ($paginator, &$productArray, &$indexPage) {
            $paginator->current()->willReturn(array_slice($productArray, $indexPage * 10, 10));
            $indexPage++;
        });
        $paginator->rewind()->shouldBeCalled()->will(function () use (&$indexPage) {
            $indexPage = 0;
        });
        $paginator->valid()->shouldBeCalled()->will(function () use (&$indexPage) {
            return $indexPage < 2;
        });
        $paginatorFactory->createPaginator($cursor)->shouldBeCalled()->willReturn($paginator);
        $subjectSet->getSubjectsCursor()->shouldBeCalled()->willReturn($cursor);

        $productUpdater
            ->copyValue($productArray, 'sku', 'description', 'fr_FR', 'fr_CH', 'ecommerce', 'tablet')
            ->shouldBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate($validProduct)->shouldBeCalled()->willReturn($emptyViolationList);
        $emptyViolationList->count()->willReturn(0);

        $productValidator->validate($invalidProduct)->shouldBeCalled()->willReturn($notEmptyViolationList);
        $notEmptyViolationList->count()->willReturn(1);
        $notEmptyViolationList->getIterator()->willReturn(new \ArrayIterator([]));

        $objectDetacher->detach($invalidProduct)->shouldBeCalled();
        $subjectSet->skipSubject($invalidProduct, Argument::any())->shouldBeCalled();
        $subjectSet->skipSubject($validProduct, Argument::any())->shouldNotBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
    }

    function it_applies_a_rule_which_has_an_unknown_action(
        $eventDispatcher,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator,
        CursorInterface $cursor
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([new \stdClass()]);
        $rule->getCode()->willReturn('test_rule');

        // paginator mocking
        $productArray = [$selectedProduct];
        $indexPage = 0;
        $paginator->current()->willReturn(array_slice($productArray, $indexPage * 10, 10));
        $paginator->rewind()->shouldBeCalled()->will(function () use (&$indexPage) {
            $indexPage = 0;
        });
        $paginator->valid()->shouldBeCalled()->will(function () use (&$indexPage) {
            return $indexPage < 2;
        });
        $paginatorFactory->createPaginator($cursor)->shouldBeCalled()->willReturn($paginator);
        $subjectSet->getSubjectsCursor()->shouldBeCalled()->willReturn($cursor);

        $this->shouldThrow(new \LogicException('The action "stdClass" is not supported yet.'))
            ->during('apply', [$rule, $subjectSet]);
    }
}
