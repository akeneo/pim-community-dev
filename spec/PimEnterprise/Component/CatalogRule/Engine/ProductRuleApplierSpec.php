<?php

namespace spec\PimEnterprise\Component\CatalogRule\Engine;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver;
use PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsUpdater;
use PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsValidator;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductRuleApplierSpec extends ObjectBehavior
{
    function let(
        PaginatorFactoryInterface $paginatorFactory,
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $paginatorFactory,
            $productsUpdater,
            $productsValidator,
            $productsSaver,
            $eventDispatcher,
            $objectDetacher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier');
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

    function it_applies_a_rule_on_valid_products(
        $eventDispatcher,
        $productsUpdater,
        $productsValidator,
        $productsSaver,
        $objectDetacher,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        PaginatorFactoryInterface $paginatorFactory,
        PaginatorInterface $paginator,
        CursorInterface $cursor,
        ProductInterface $validProduct1,
        ProductInterface $validProduct2
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
        $productsValidator->validate($rule, Argument::any())->willReturn([$validProduct1, $validProduct2]);
        $productsSaver->save($rule, [$validProduct1, $validProduct2])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $objectDetacher->detach($selectedProduct)->shouldBeCalled();
    }
}
