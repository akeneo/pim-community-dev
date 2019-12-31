<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionLabelSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('Magento Connector');
        $this->shouldBeAnInstanceOf(ConnectionLabel::class);
    }

    public function it_cannot_contains_a_string_shorter_than_3_characters()
    {
        $this->beConstructedWith('aa');
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.too_short')
        )->duringInstantiation();
    }

    public function it_cannot_contains_a_string_longer_than_100_characters()
    {
        $this->beConstructedWith(str_repeat('a', 101));
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.too_long')
        )->duringInstantiation();
    }

    public function it_returns_the_connection_label_as_a_string()
    {
        $this->beConstructedWith('Magento Connector');
        $this->__toString()->shouldReturn('Magento Connector');
    }

    public function it_cannot_contains_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(
            new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.required')
        )->duringInstantiation();
    }
}
