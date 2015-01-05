<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Component\Persistence\BulkSaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;

class ProductRuleApplierSpec extends ObjectBehavior
{

    /** @var const string */
    const RULE_DEFINITION_CLASS = 'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition';

    public function let(
        EventDispatcherInterface $eventDispatcher,
        ProductUpdaterInterface $productUpdater,
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        ValidatorInterface $productValidator,
        ObjectManager $objectManager,
        CacheClearer $cacheClearer
    ) {
        $this->beConstructedWith(
            $productUpdater,
            $productValidator,
            $productSaver,
            $eventDispatcher,
            $objectManager,
            $versionManager,
            $cacheClearer,
            self::RULE_DEFINITION_CLASS
        );
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
        RuleSubjectSetInterface $subjectSet,
        $cacheClearer
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
        $productSaver->saveAll([], ['recalculate' => false, 'schedule' => true])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();
        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

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
        ConstraintViolationList $violationList,
        $cacheClearer
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
        $versionManager->setRealTimeVersioning(false)->shouldBeCalled();
        $productSaver->saveAll([$selectedProduct], ['recalculate' => false, 'schedule' => true])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
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
        ConstraintViolationList $violationList,
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
        $versionManager->setRealTimeVersioning(false)->shouldBeCalled();
        $productSaver->saveAll([$selectedProduct], ['recalculate' => false, 'schedule' => true])->shouldBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
    }

    public function it_applies_a_rule_with_invalid_product(
        $eventDispatcher,
        $productUpdater,
        $productValidator,
        $objectManager,
        RuleInterface $rule,
        RuleSubjectSetInterface $subjectSet,
        ProductCopyValueAction $action,
        ProductInterface $validProduct,
        ProductInterface $invalidProduct,
        ConstraintViolationList $emptyViolationList,
        ConstraintViolationList $notEmptyViolationList,
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
        $subjectSet->getSubjects()->willReturn([$validProduct, $invalidProduct]);
        $productUpdater
            ->copyValue([$validProduct, $invalidProduct], 'sku', 'description', 'fr_FR', 'fr_CH', 'ecommerce', 'tablet')
            ->shouldBeCalled();

        // validate products
        $rule->getCode()->willReturn('rule_one');
        $productValidator->validate($validProduct)->shouldBeCalled()->willReturn($emptyViolationList);
        $emptyViolationList->count()->willReturn(0);

        $productValidator->validate($invalidProduct)->shouldBeCalled()->willReturn($notEmptyViolationList);
        $notEmptyViolationList->count()->willReturn(1);
        $notEmptyViolationList->getIterator()->willReturn(new \ArrayIterator([]));

        $objectManager->detach($invalidProduct)->shouldBeCalled();
        $subjectSet->skipSubject($invalidProduct, Argument::any())->shouldBeCalled();
        $subjectSet->skipSubject($validProduct, Argument::any())->shouldNotBeCalled();

        $eventDispatcher->dispatch(RuleEvents::POST_APPLY, Argument::any())->shouldBeCalled();

        $this->apply($rule, $subjectSet);

        $cacheClearer->addNonClearableEntity(self::RULE_DEFINITION_CLASS)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();
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
