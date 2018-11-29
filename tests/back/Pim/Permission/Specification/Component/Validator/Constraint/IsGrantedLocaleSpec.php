<?php

namespace Specification\Akeneo\Pim\Permission\Component\Validator\Constraint;

use Akeneo\Pim\Permission\Component\Validator\Constraint\IsGrantedLocale;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class IsGrantedLocaleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsGrantedLocale::class);
        $this->shouldBeAnInstanceOf(Constraint::class);
    }
}
