<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Model;

use Akeneo\EnrichedEntity\Domain\Model\Image;
use PhpSpec\ObjectBehavior;

class ImageSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['/path/image.jpg']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Image::class);
    }

    public function it_returns_the_key()
    {
        $this->getKey()->shouldReturn('/path/image.jpg');
    }
}
