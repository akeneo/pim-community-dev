<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorIdSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromString', ['2038e1c9-68ff-4833-b06f-01e42d206002']);
    }

    public function it_is_an_identifier_generator_id(): void
    {
        $this->shouldBeAnInstanceOf(IdentifierGeneratorId::class);
    }

    public function it_returns_an_identifier_generator_id(): void
    {
        $this->asString()->shouldReturn('2038e1c9-68ff-4833-b06f-01e42d206002');
    }

    public function it_cannot_be_instantiated_with_not_uuid(): void
    {
        $this->beConstructedThrough('fromString', ['not_uuid']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
