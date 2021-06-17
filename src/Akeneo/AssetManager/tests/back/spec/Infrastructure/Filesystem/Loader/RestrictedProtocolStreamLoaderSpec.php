<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use Akeneo\AssetManager\Infrastructure\Filesystem\Loader\RestrictedProtocolStreamLoader;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use PhpSpec\ObjectBehavior;

class RestrictedProtocolStreamLoaderSpec extends ObjectBehavior
{
    function let(LoaderInterface $loader)
    {
        $this->beConstructedWith($loader, ['http', 'https']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RestrictedProtocolStreamLoader::class);
        $this->shouldImplement(LoaderInterface::class);
    }

    function it_throws_an_exception_when_protocol_is_not_allowed()
    {
        $this->shouldThrow(NotLoadableException::class)->during('find', ['file://a_file.png']);
    }

    function it_does_not_throw_exception_when_protocol_is_valid(LoaderInterface $loader)
    {
        $loader->find('http://www.example.com/a_file.png')->shouldBeCalledOnce();
        $this->find('http://www.example.com/a_file.png');
    }

    function it_checks_the_protocol_in_case_insensitive(LoaderInterface $loader)
    {
        $loader->find('HTtps://www.example.com/a_file.png')->shouldBeCalledOnce();
        $this->find('HTtps://www.example.com/a_file.png');
    }

    function it_does_not_throw_exception_when_path_is_relative(LoaderInterface $loader)
    {
        $loader->find('/a_file.png')->shouldBeCalledOnce();
        $this->find('/a_file.png');
    }
}
