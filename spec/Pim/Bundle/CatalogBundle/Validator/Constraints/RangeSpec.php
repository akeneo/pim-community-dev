<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class RangeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\Range');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_a_min_date_message()
    {
        $this->beConstructedWith(['min' => new \DateTime()]);
        $this->minDateMessage->shouldBe('This date should be {{ limit }} or after.');
    }

    function it_has_a_max_date_message()
    {
        $this->beConstructedWith(['max' => new \DateTime()]);
        $this->maxDateMessage->shouldBe('This date should be {{ limit }} or before.');
    }

    function it_has_an_invalid_date_message()
    {
        $this->beConstructedWith(['min' => new \DateTime()]);
        $this->invalidDateMessage->shouldBe('This value is not a valid date.');
    }
}
