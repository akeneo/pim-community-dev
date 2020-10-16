<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ConnectionMustExist;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class ConnectionMustExistSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConnectionMustExist::class);
    }

    public function it_is_a_constraint(): void
    {
        $this->shouldHaveType(Constraint::class);
    }

    public function it_provides_a_target(): void
    {
        $this->getTargets()->shouldReturn(ConnectionMustExist::PROPERTY_CONSTRAINT);
    }

    public function it_provides_a_tag_to_be_validated(): void
    {
        $this->validatedBy()->shouldReturn('connection_must_exist');
    }
}
