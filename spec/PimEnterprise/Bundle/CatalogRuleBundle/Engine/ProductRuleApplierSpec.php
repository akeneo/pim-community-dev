<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Akeneo\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\PaginatorInterface;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ObjectDetacherInterface;
use Akeneo\Component\Persistence\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsSaver;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsValidator;
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
        PaginatorFactoryInterface $paginatorFactory,
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        CacheClearer $cacheClearer
    ) {
        $this->beConstructedWith(
            $paginatorFactory,
            $productsUpdater,
            $productsValidator,
            $productsSaver,
            $eventDispatcher,
            $cacheClearer,
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

    function it_applies_a_rule_which_does_not_select_products(
        $eventDispatcher,
        $productsUpdater,
        $productsValidator,
        $productsSaver,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        CursorInterface $cursor,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        $rule->getActions()->willReturn([]);

        $paginator->valid()->shouldBeCalled()->willReturn(false);
        $paginator->rewind()->shouldBeCalled()->willReturn(null);
        $paginatorFactory->createPaginator($cursor)->shouldBeCalled()->willReturn($paginator);
        $subjectSet->getSubjectsCursor()->shouldBeCalled()->willReturn($cursor);

        $productsUpdater->update(Argument::any(), Argument::any())->shouldNotBeCalled();
        $productsValidator->validate(Argument::any(), Argument::any())->shouldNotBeCalled();
        $productsSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_on_products(
        $eventDispatcher,
        $productsUpdater,
        $productsValidator,
        $productsSaver,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator,
        CursorInterface $cursor,
        $cacheClearer
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

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

        $productsUpdater->update($rule, Argument::any())->shouldBeCalled();
        $productsValidator->validate($rule, Argument::any())->shouldBeCalled();
        $productsSaver->save($rule, Argument::any())->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
    }
}
