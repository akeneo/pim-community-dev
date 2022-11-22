<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FreeTextSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromString', ['ABC']);
    }

    public function it_is_a_free_text(): void
    {
        $this->shouldBeAnInstanceOf(FreeText::class);
    }

    public function it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_a_free_text(): void
    {
        $this->asString()->shouldReturn('ABC');
    }

    public function it_normalize_a_free_text(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'free_text',
            'string' => 'ABC',
        ]);
    }

    public function it_creates_from_normalized(): void
    {
        $this->fromNormalized([
            'type' => 'free_text',
            'string' => 'ABC',
        ])->shouldBeLike(FreeText::fromString('ABC'));
    }

    public function it_throws_an_exception_when_type_is_bad(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'bad',
            'string' => 'ABC',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_type_key_is_missing(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'string' => 'ABC',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_from_normalized_with_empty_string(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'free_text',
            'string' => '',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
