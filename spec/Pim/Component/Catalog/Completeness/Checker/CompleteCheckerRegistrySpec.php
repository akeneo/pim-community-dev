<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\Checker\Attribute\AttributeCompleteCheckerInterface;

class CompleteCheckerRegistrySpec extends ObjectBehavior
{
    public function it_is_a_completeCheckerRegistry()
    {
        $this->shouldImplement(
            'Pim\Component\Catalog\Completeness\Checker\CompleteCheckerRegistryInterface'
        );
    }

    public function it_register_attribute_checker(AttributeCompleteCheckerInterface $attributeCompleteChecker)
    {
        $this->registerAttributeChecker($attributeCompleteChecker);
        $this->getAttributeCheckers()->shouldReturn([$attributeCompleteChecker]);

        $this->shouldThrow('\PhpSpec\Exception\Example\ErrorException')
            ->duringRegisterAttributeChecker('other_value');
        $this->shouldThrow('\PhpSpec\Exception\Example\ErrorException')
            ->duringRegisterAttributeChecker(new \StdClass());
    }
}
