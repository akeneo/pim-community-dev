<?php

namespace spec\Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData\EmptyCheckerInterface;

class ChainedEmptyCheckerSpec extends ObjectBehavior
{
    function it_is_a_empty_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData\EmptyCheckerInterface');
    }

    function it_contains_checkers(
        EmptyCheckerInterface $emptyCheckerOne,
        EmptyCheckerInterface $emptyCheckerTwo
    ) {
        $this->addEmptyChecker($emptyCheckerOne)->shouldReturn($this);
        $this->addEmptyChecker($emptyCheckerTwo)->shouldReturn($this);
    }

    function it_supports_attribute_code(EmptyCheckerInterface $checker)
    {
        $this->addEmptyChecker($checker)->shouldReturn($this);
        $checker->supports('att_code')->shouldBeCalled();
        $this->supports('att_code');
    }

    function it_checks_not_empty_product_value_data(
        EmptyCheckerInterface $baseChecker
    ) {
        $this->addEmptyChecker($baseChecker)->shouldReturn($this);
        $baseChecker->supports('att_code')->willReturn(true);
        $baseChecker->isEmpty('att_code', 'not empty')->willReturn(false);
        $this->isEmpty('att_code', 'not empty')->shouldReturn(false);
    }

    function it_checks_empty_product_value_data(
        EmptyCheckerInterface $baseChecker
    ) {
        $this->addEmptyChecker($baseChecker)->shouldReturn($this);
        $baseChecker->supports('att_code')->willReturn(true);
        $baseChecker->isEmpty('att_code', '')->willReturn(true);
        $this->isEmpty('att_code', '')->shouldReturn(true);
    }

    function it_throws_exception_when_no_checker_supports_the_value() {
        $this->shouldThrow(
            new \LogicException(
                'No compatible EmptyCheckerInterface found for attribute "not_supported_attribute_code".'
            )
        )->during(
            'isEmpty',
            ['not_supported_attribute_code', 'my data', []]
        );
    }
}
