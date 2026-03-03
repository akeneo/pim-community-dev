<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionTypeSpec extends ObjectBehavior
{
    public function it_is_instantiable(): void
    {
        $this->beConstructedWith('connection_type');
        $this->shouldBeAnInstanceOf(ConnectionType::class);
    }

    public function it_cannot_contain_an_empty_string(): void
    {
        $exceptionMessage = 'akeneo_connectivity.connection.connection.constraint.type.required';
        $this->beConstructedWith('');
        $this->shouldThrow(new \InvalidArgumentException($exceptionMessage))->duringInstantiation();
    }

    public function it_cannot_contain_a_string_longer_than_30_characters(): void
    {
        $exceptionMessage = 'akeneo_connectivity.connection.connection.constraint.type.too_long';
        $this->beConstructedWith(\str_repeat('a', 31));
        $this->shouldThrow(new \InvalidArgumentException($exceptionMessage))->duringInstantiation();
    }

    public function it_implements_to_string_and_returns_connection_type(): void
    {
        $this->beConstructedWith('connection_type');
        $this->__toString()->shouldReturn('connection_type');
    }

    public function it_returns_a_default_value(): void
    {
        $this->beConstructedWith(null);
        $this->__toString()->shouldReturn('default');
    }
}
