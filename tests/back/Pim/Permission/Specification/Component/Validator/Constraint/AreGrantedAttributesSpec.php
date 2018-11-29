<?php

namespace Specification\Akeneo\Pim\Permission\Component\Validator\Constraint;

use Akeneo\Pim\Permission\Component\Validator\Constraint\AreGrantedAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class AreGrantedAttributesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AreGrantedAttributes::class);
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
}
