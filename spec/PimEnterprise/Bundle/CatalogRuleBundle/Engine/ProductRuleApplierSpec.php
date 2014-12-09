<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Resource\Model\BulkSaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

class ProductRuleApplierSpec extends ObjectBehavior
{
    public function let(
        EventDispatcherInterface $eventDispatcher,
        ProductUpdaterInterface $productUpdater,
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        ValidatorInterface $productValidator
    ) {
        $this->beConstructedWith($productUpdater, $productValidator, $productSaver, $eventDispatcher, $versionManager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier');
    }

    public function it_is_a_rule_applier()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface');
    }

    public function it_applies_a_rule_which_does_not_update_products(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        $productSaver,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        // update products
        $rule->getActions()->willReturn([]);
        $subjectSet->getSubjects()->willReturn([]);
        $productUpdater->setValue(Argument::any())->shouldNotBeCalled();
        $productUpdater->copyValue(Argument::any())->shouldNotBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate(Argument::any())->shouldNotBeCalled();

        // save products
        $productSaver->saveAll([])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $this->apply($rule, $subjectSet);
    }

    public function it_applies_a_rule_which_has_a_set_action(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        $productSaver,
        $versionManager,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductSetValueActionInterface $action,
        ProductInterface $selectedProduct,
        ConstraintViolationList $violationList
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();

        // update products
        $action->getField()->willReturn('sku');
        $action->getValue()->willReturn('foo');
        $action->getScope()->willReturn('ecommerce');
        $action->getLocale()->willReturn('en_US');
        $rule->getActions()->willReturn([$action]);
        $subjectSet->getSubjects()->willReturn([$selectedProduct]);
        $productUpdater->setValue([$selectedProduct], 'sku', 'foo', 'en_US', 'ecommerce')->shouldBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate($selectedProduct)->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);

        // save products
        $versionManager->setContext('Applied rule "rule_one"')->shouldBeCalled();
        $productSaver->saveAll([$selectedProduct])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    public function it_applies_a_rule_which_has_a_copy_action(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        $productSaver,
        $versionManager,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductCopyValueAction $action,
        ProductInterface $selectedProduct,
        ConstraintViolationList $violationList
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
        $subjectSet->getSubjects()->willReturn([$selectedProduct]);
        $productUpdater
            ->copyValue([$selectedProduct], 'sku', 'description', 'fr_FR', 'fr_CH', 'ecommerce', 'tablet')
            ->shouldBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate($selectedProduct)->shouldBeCalled()->willReturn($violationList);
        $violationList->count()->willReturn(0);

        // save products
        $versionManager->setContext('Applied rule "rule_one"')->shouldBeCalled();
        $productSaver->saveAll([$selectedProduct])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);
    }

    public function it_applies_a_rule_which_has_an_unknown_action(
        $eventDispatcher,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet
    ) {
        $eventDispatcher->dispatch(RuleEvents::PRE_APPLY, Argument::any())->shouldBeCalled();
        $rule->getActions()->willReturn([new \stdClass()]);

        $this->shouldThrow(new \LogicException('The action "stdClass" is not supported yet.'))
            ->during('apply', [$rule, $subjectSet]);
    }
}
