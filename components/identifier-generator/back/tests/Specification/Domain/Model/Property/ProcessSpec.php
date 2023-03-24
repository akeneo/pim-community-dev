<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProcessSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'type' => 'truncate',
                'operator' => '=',
                'value' => 3,
            ],
        ]);
    }

    public function it_is_a_family_generation_process(): void
    {
        $this->shouldBeAnInstanceOf(Process::class);
    }

    public function it_returns_a_type(): void
    {
        $this->type()->shouldReturn('truncate');
    }

    public function it_normalize_a_process(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'truncate',
            'operator' => '=',
            'value' => 3,
        ]);
    }

    public function it_should_throw_an_exception_when_no_type(): void
    {
        $this->beConstructedThrough('fromNormalized', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_invalid_type(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'unknown']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_throw_an_exception_when_type_no_is_well_formed(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'no']]);
        $this->shouldNotThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_type_truncate_and_no_operator(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'truncate', 'value' => 3]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_type_truncate_and_empty_operator(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'truncate', 'operator' => null, 'value' => 3]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_type_truncate_and_unknown_operator(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'truncate', 'operator' => 'unknown', 'value' => 3]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_type_truncate_and_no_value(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'truncate', 'operator' => '=']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_type_truncate_and_not_numeric_value(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'truncate', 'operator' => '=', 'value' => 'bar']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_type_truncate_and_too_high_value(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'truncate', 'operator' => '=', 'value' => 6]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_an_exception_when_type_truncate_and_too_low_value(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'truncate', 'operator' => '=', 'value' => 0]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_throw_an_exception_when_type_nomenclature_is_well_formed(): void
    {
        $this->beConstructedThrough('fromNormalized', [['type' => 'nomenclature']]);
        $this->shouldNotThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
