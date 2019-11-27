<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\ValueObject;

use Akeneo\Apps\Domain\Model\ValueObject\AppImage;
use PhpSpec\ObjectBehavior;

class AppImageSpec extends ObjectBehavior
{
    public function it_is_an_app_image(): void
    {
        $this->beConstructedWith('a/b/c/image_path.png');
        $this->shouldHaveType(AppImage::class);
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
            ->shouldThrow(new \InvalidArgumentException('akeneo_apps.app.constraint.image.not_empty'))
            ->duringInstantiation();
    }
}
