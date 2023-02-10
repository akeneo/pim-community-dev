<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextTransformationSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromString', ['no']);
    }

    public function it_is_a_target(): void
    {
        $this->shouldBeAnInstanceOf(TextTransformation::class);
    }

    public function it_cannot_be_instantiated_with_an_unknown_string(): void
    {
        $this->beConstructedThrough('fromString', ['unknown']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_a_text_transformation(): void
    {
        $this->normalize()->shouldReturn('no');
    }
}
