<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\IsConnectionsNumberLimitReached;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IsConnectionsNumberLimitReached::class);
    }

    public function it_is_a_constraint(): void
    {
        $this->shouldHaveType(Constraint::class);
    }

    public function it_provides_a_target(): void
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }

    public function it_has_a_message(): void
    {
        $this->message->shouldBeString();
    }
}
