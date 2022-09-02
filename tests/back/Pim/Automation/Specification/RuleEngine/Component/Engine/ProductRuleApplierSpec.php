<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsSaver;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Pim\Automation\RuleEngine\Component\Event\SubjectsWereSkippedWithNoUpdate;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SavedSubjectsEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductRuleApplierSpec extends ObjectBehavior
{
    function let(
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(Argument::type('object'));
        $this->beConstructedWith(
            $productsUpdater,
            $productsValidator,
            $productsSaver,
            $eventDispatcher,
            $cacheClearer,
            10
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRuleApplier::class);
    }

    function it_is_a_rule_applier()
    {
        $this->shouldHaveType(ApplierInterface::class);
    }

    function it_applies_a_rule_which_does_not_select_products(
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerClearerInterface $cacheClearer,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        CursorInterface $cursor
    ) {
        $eventDispatcher->dispatch(Argument::type(SelectedRuleEvent::class), RuleEvents::PRE_APPLY)->shouldBeCalled();

        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(false);
        $subjectSet->getSubjectsCursor()->willReturn($cursor);

        $productsUpdater->update(Argument::any(), Argument::any())->shouldNotBeCalled();
        $productsValidator->validate(Argument::any(), Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SubjectsWereSkippedWithNoUpdate::class))->shouldNotBeCalled();
        $productsSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();
        $cacheClearer->clear()->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SavedSubjectsEvent::class), Argument::any())->shouldNotBeCalled();

        $eventDispatcher->dispatch(Argument::type(SelectedRuleEvent::class), RuleEvents::POST_APPLY)->shouldBeCalled();
        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_on_valid_products(
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerClearerInterface $cacheClearer,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        CursorInterface $cursor,
        ProductInterface $validProduct1,
        ProductInterface $validProduct2
    ) {
        $indexPage = 0;
        $validProduct1->isDirty()->willReturn(true);
        $validProduct2->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(SelectedRuleEvent::class), RuleEvents::PRE_APPLY)->shouldBeCalled();

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
        $productsUpdater->update($rule, Argument::any())->shouldBeCalled()->willReturn([$validProduct1, $validProduct2]);
        $eventDispatcher->dispatch(Argument::type(SavedSubjectsEvent::class), RuleEvents::PRE_SAVE_SUBJECTS)->shouldBeCalled();
        $productsValidator->validate($rule, Argument::any())->willReturn([$validProduct1, $validProduct2]);
        $eventDispatcher->dispatch(Argument::type(SubjectsWereSkippedWithNoUpdate::class))->shouldNotBeCalled();
        $productsSaver->save($rule, [$validProduct1, $validProduct2])->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(SavedSubjectsEvent::class), RuleEvents::POST_SAVE_SUBJECTS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(SelectedRuleEvent::class), RuleEvents::POST_APPLY)->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_on_every_product_in_the_subject_set(
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerClearerInterface $cacheClearer,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        ProductInterface $newProduct,
        CursorInterface $cursor
    ) {
        $selectedProduct->isDirty()->willReturn(true);
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

        $newProduct->isDirty()->willReturn(true);
        $productsUpdater->update($rule, Argument::type('array'))->shouldBeCalledTimes(5)->willReturn([$newProduct]);
        $productsValidator->validate($rule, Argument::type('array'))->shouldBeCalledTimes(5)->willReturnArgument(1);
        $eventDispatcher->dispatch(Argument::type(SubjectsWereSkippedWithNoUpdate::class))->shouldNotBeCalled();
        $productsSaver->save($rule, Argument::type('array'))->shouldBeCalledTimes(5);
        $cacheClearer->clear()->shouldBeCalledTimes(5);

        $this->apply($rule, $subjectSet);
    }

    function it_applies_a_rule_on_products_with_no_diff(
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        ProductsSaver $productsSaver,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerClearerInterface $cacheClearer,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $selectedProduct,
        CursorInterface $cursor,
        ProductInterface $validProduct1,
        ProductInterface $product2
    ) {
        $indexPage = 0;
        $validProduct1->isDirty()->willReturn(true);
        $product2->isDirty()->willReturn(false);

        $eventDispatcher->dispatch(Argument::type(SelectedRuleEvent::class), RuleEvents::PRE_APPLY)->shouldBeCalled();

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
        $productsUpdater->update($rule, Argument::any())->shouldBeCalled()->willReturn([$validProduct1, $product2]);
        $eventDispatcher->dispatch(Argument::type(SavedSubjectsEvent::class), RuleEvents::PRE_SAVE_SUBJECTS)->shouldBeCalled();
        $productsValidator->validate($rule, Argument::any())->willReturn([$validProduct1, $product2]);
        $eventDispatcher->dispatch(
            Argument::that(
                fn ($argument): bool =>
                    $argument instanceof SubjectsWereSkippedWithNoUpdate
                    && $argument->getSkippedSubjects() === [$product2->getWrappedObject()]
            )
        )->shouldBeCalled();
        $productsSaver->save($rule, [$validProduct1])->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(SavedSubjectsEvent::class), RuleEvents::POST_SAVE_SUBJECTS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(SelectedRuleEvent::class), RuleEvents::POST_APPLY)->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }
}
