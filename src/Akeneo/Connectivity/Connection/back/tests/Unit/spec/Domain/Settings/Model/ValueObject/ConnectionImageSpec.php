<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use PhpSpec\ObjectBehavior;

class ConnectionImageSpec extends ObjectBehavior
{
    public function it_is_a_connection_image(): void
    {
        $this->beConstructedWith('a/b/c/image_path.png');
        $this->shouldHaveType(ConnectionImage::class);
    }

    public function it_provides_a_file_path()
    {
        $this->beConstructedWith('a/b/c/image_path.png');
        $this->__toString()->shouldReturn('a/b/c/image_path.png');
    }

    public function it_throws_an_error_if_file_path_is_empty()
    {
        $this->beConstructedWith('');
        $this
            ->shouldThrow(new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.image.not_empty'))
            ->duringInstantiation();
    }
}
