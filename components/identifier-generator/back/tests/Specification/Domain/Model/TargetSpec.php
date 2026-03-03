<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromString', ['sku']);
    }

    public function it_is_a_target(): void
    {
        $this->shouldBeAnInstanceOf(Target::class);
    }

    public function it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_a_target(): void
    {
        $this->asString()->shouldReturn('sku');
    }
}
