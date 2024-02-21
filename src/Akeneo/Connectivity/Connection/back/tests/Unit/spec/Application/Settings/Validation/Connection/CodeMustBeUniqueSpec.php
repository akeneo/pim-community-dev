<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class CodeMustBeUniqueSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CodeMustBeUnique::class);
    }

    public function it_is_a_constraint(): void
    {
        $this->shouldHaveType(Constraint::class);
    }

    public function it_provides_a_target(): void
    {
        $this->getTargets()->shouldReturn(CodeMustBeUnique::PROPERTY_CONSTRAINT);
    }

    public function it_provides_a_tag_to_be_validated(): void
    {
        $this->validatedBy()->shouldReturn('connection_code_must_be_unique');
    }
}
