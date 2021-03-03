<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RestrictedProtocolStreamLoaderSpec extends ObjectBehavior
{
    public function let(LoaderInterface $loader)
    {
        $this->beConstructedWith($loader, ['http://', 'https://']);
    }

    public function it_call_loader_when_protocol_is_allowed(LoaderInterface $loader, BinaryInterface $loadedImage)
    {
        $loader->find('http://my_awesome_site.fr/my_image.png')->shouldBeCalled()->willReturn($loadedImage);

        $this->find('http://my_awesome_site.fr/my_image.png');
    }

    public function it_throw_an_exception_when_protocol_is_not_allowed(LoaderInterface $loader)
    {
        $loader->find(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(NotLoadableException::class)->during('find', ['file://my_awesome_site.fr']);
    }
}
