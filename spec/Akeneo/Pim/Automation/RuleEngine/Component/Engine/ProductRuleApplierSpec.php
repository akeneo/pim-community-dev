<?php

namespace spec\Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsSaver;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
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
        ObjectDetacherInterface $objectDetacher,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->beConstructedWith(
            $paginatorFactory,
            $productsUpdater,
            $productsValidator,
            $productsSaver,
            $eventDispatcher,
            $objectDetacher,
            $cacheClearer,
            10
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
        $objectDetacher,
        $cacheClearer,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        CursorInterface $cursor
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(false);
        $subjectSet->getSubjectsCursor()->willReturn($cursor);

        $productsUpdater->update(Argument::any(), Argument::any())->shouldNotBeCalled();
        $productsValidator->validate(Argument::any(), Argument::any())->shouldNotBeCalled();
        $productsSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();
        $objectDetacher->detach(Argument::any())->shouldNotBeCalled();

        $objectDetacher->detach(Argument::any())->shouldNotBeCalled();
        $cacheClearer->clear()->shouldNotBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_on_valid_products(
        $eventDispatcher,
        $productsUpdater,
        $productsValidator,
        $productsSaver,
        $objectDetacher,
        $cacheClearer,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        CursorInterface $cursor,
        ProductInterface $validProduct1,
        ProductInterface $validProduct2
    ) {
        $indexPage = 0;

        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        $cursor->rewind()->will(
            function () use (&$indexPage) {
                $indexPage = 0;
            }
        );
        $cursor->current()->willReturn($selectedProduct);
        $cursor->next()->will(
            function () use (&$indexPage) {
                $indexPage++;
            }
        );
        $cursor->valid()->will(
            function () use (&$indexPage) {
                return $indexPage < 13;
            }
        );
        $subjectSet->getSubjectsCursor()->willReturn($cursor);
        $productsUpdater->update($rule, Argument::any())->shouldBeCalled();
        $productsValidator->validate($rule, Argument::any())->willReturn([$validProduct1, $validProduct2]);
        $productsSaver->save($rule, [$validProduct1, $validProduct2])->shouldBeCalled();
        $objectDetacher->detach(Argument::any())->shouldNotBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_on_every_product_in_the_subject_set(
        $productsUpdater,
        $productsValidator,
        $productsSaver,
        $objectDetacher,
        $cacheClearer,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        CursorInterface $cursor
    ) {
        $indexPage = 0;
        $cursor->rewind()->will(
            function () use (&$indexPage) {
                $indexPage = 0;
            }
        );
        $cursor->current()->willReturn($selectedProduct);
        $cursor->next()->will(
            function () use (&$indexPage) {
                $indexPage++;
            }
        );
        $cursor->valid()->will(
            function () use (&$indexPage) {
                return $indexPage < 42;
            }
        );
        $subjectSet->getSubjectsCursor()->willReturn($cursor);

        $productsUpdater->update($rule, Argument::type('array'))->shouldBeCalledTimes(5);
        $productsValidator->validate($rule, Argument::type('array'))->shouldBeCalledTimes(5)->willReturnArgument(1);
        $productsSaver->save($rule, Argument::type('array'))->shouldBeCalledTimes(5);
        $objectDetacher->detach(Argument::any())->shouldNotBeCalled();
        $cacheClearer->clear()->shouldBeCalledTimes(5);

        $this->apply($rule, $subjectSet);
    }
}
