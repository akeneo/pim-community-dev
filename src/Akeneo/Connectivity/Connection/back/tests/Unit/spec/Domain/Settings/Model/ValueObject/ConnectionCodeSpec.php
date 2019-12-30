<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionCodeSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('magento');
        $this->shouldBeAnInstanceOf(ConnectionCode::class);
    }

    public function it_cannot_contains_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.code.required'))->duringInstantiation();
    }

    public function it_cannot_contains_a_string_shorter_than_3_characters()
    {
        $this->beConstructedWith('aa');
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.code.too_short')
        )->duringInstantiation();
    }

    public function it_cannot_contains_a_string_longer_than_100_characters()
    {
        $this->beConstructedWith(str_repeat('a', 103));
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.code.too_long')
        )->duringInstantiation();
    }

    public function it_contains_only_alphanumeric_characters()
    {
        $this->beConstructedWith('magento-connector');
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.code.invalid')
        )->duringInstantiation();
    }

    public function it_returns_the_connection_code_as_a_string()
    {
        $this->beConstructedWith('magento');
        $this->__toString()->shouldReturn('magento');
    }
}
