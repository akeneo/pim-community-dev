<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class ImageSpec extends ObjectBehavior
{
    public function let()
    {
        $file = new FileInfo();
        $file->setKey('/path/image.jpg');
        $file->setOriginalFilename('image.jpg');

        $this->beConstructedThrough('fromFileInfo', [$file]);
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
