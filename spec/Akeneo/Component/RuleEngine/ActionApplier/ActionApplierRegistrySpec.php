<?php

namespace spec\Akeneo\Component\RuleEngine\ActionApplier;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use PhpSpec\ObjectBehavior;

class ActionApplierRegistrySpec extends ObjectBehavior
{
    function it_provides_action_applier(ActionApplierInterface $setterActionApplier, ActionApplierInterface $copierActionApplier, ActionInterface $action)
    {
        $this->addActionApplier($setterActionApplier);
        $this->addActionApplier($copierActionApplier);

        $setterActionApplier->supports($action)->willReturn(false);
        $copierActionApplier->supports($action)->willReturn(true);

        $this->getActionApplier($action)->shouldReturn($copierActionApplier);
    }

    function it_throws_exception_if_no_provider_supports_the_given_action(ActionApplierInterface $setterActionApplier, ActionInterface $action)
    {
        $this->addActionApplier($setterActionApplier);
        $setterActionApplier->supports($action)->willReturn(false);

        $this->shouldThrow(new \LogicException(sprintf('The action "%s" is not supported yet.', get_class($action->getWrappedObject()))))
            ->during('getActionApplier', [$action]);
    }
}
