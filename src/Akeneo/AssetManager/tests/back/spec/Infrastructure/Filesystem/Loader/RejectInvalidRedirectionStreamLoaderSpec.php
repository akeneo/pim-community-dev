<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use Akeneo\AssetManager\Infrastructure\Network\UrlChecker;
use GuzzleHttp\Psr7\Uri;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RejectInvalidRedirectionStreamLoaderSpec extends ObjectBehavior
{
    function let(UrlChecker $urlChecker)
    {
        $this->beConstructedWith($urlChecker);
    }

    function it_allows_a_redirect_when_protocol_and_domain_are_valid(
        RequestInterface $request,
        ResponseInterface $response,
        UrlChecker $urlChecker
    ) {
        $urlChecker->isProtocolAllowed('https')->shouldBeCalled()->willReturn(true);
        $urlChecker->isDomainAllowed('example.com')->shouldBeCalled()->willReturn(true);

        $this->checkRedirectIsValid(
            $request,
            $response,
            new Uri('https://example.com/image.jpg')
        )->shouldReturn(true);
    }

    function it_denies_a_uri_with_invalid_protocol(
        RequestInterface $request,
        ResponseInterface $response,
        UrlChecker $urlChecker
    ) {
        $urlChecker->isProtocolAllowed('file')->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(NotLoadableException::class)->during('checkRedirectIsValid', [
            $request,
            $response,
            new Uri('file://image.jpg')
        ]);
    }

    function it_denies_a_uri_with_invalid_domain(
        RequestInterface $request,
        ResponseInterface $response,
        UrlChecker $urlChecker
    ) {
        $urlChecker->isProtocolAllowed('https')->shouldBeCalled()->willReturn(true);
        $urlChecker->isDomainAllowed('localhost')->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(NotLoadableException::class)->during('checkRedirectIsValid', [
            $request,
            $response,
            new Uri('https://localhost/image.jpg')
        ]);
    }
}
