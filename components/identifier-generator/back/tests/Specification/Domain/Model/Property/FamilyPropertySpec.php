<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyPropertySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'type' => 'family',
                'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3]
            ]
        ]);
    }

    public function it_is_a_family_generation(): void
    {
        $this->shouldBeAnInstanceOf(FamilyProperty::class);
    }

    public function it_returns_a_type(): void
    {
        $this->type()->shouldReturn('family');
    }

    public function it_returns_a_process(): void
    {
        $process = Process::fromNormalized(['type' => 'truncate', 'operator' => '=', 'value' => 3]);
        $this->process()->shouldBeAnInstanceOf(Process::class);
        $this->process()->shouldBeLike($process);
    }

    public function it_normalize_a_family(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'family',
            'process' => [
                'type' => 'truncate',
                'operator' => '=',
                'value' => 3
            ]
        ]);
    }

    public function it_should_return_an_implicit_condition(): void
    {
        $this->getImplicitCondition()->shouldBeLike(
            Family::fromNormalized(['type' => 'family', 'operator' => 'NOT EMPTY']),
        );
    }
}
