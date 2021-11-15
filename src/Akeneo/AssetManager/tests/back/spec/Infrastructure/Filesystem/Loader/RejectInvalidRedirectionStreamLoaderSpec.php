<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use Akeneo\AssetManager\Infrastructure\Network\DnsLookup;
use Akeneo\AssetManager\Infrastructure\Network\DnsLookupInterface;
use Akeneo\AssetManager\Infrastructure\Network\IpMatcher;
use GuzzleHttp\Psr7\Uri;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RejectInvalidRedirectionStreamLoaderSpec extends ObjectBehavior
{
    function let(DnsLookupInterface $dnsLookup, IpMatcher $ipMatcher)
    {
        $this->beConstructedWith(
            ['http', 'https'],
            $dnsLookup,
            $ipMatcher,
            '192.168.1.54'
        );
    }

    function it_denies_a_uri_with_invalid_protocol(RequestInterface $request, ResponseInterface $response)
    {
        $this->shouldThrow(NotLoadableException::class)->during('checkRedirectIsValid', [
            $request,
            $response,
            new Uri('file://image.jpg')
        ]);
    }

    function it_allows_a_redirect_to_whitelisted_ip(DnsLookup $dnsLookup, IpMatcher $ipMatcher, RequestInterface $request, ResponseInterface $response)
    {
        $dnsLookup->ip('example.com')->shouldBeCalled()->willReturn('192.168.1.54');
        $ipMatcher->match('192.168.1.54', ['192.168.1.54'])->shouldBeCalled()->willReturn(true);
        $this->checkRedirectIsValid(
            $request,
            $response,
            new Uri('https://example.com/image.jpg')
        );
    }

    function it_denies_a_redirect_pointing_to_private_range(DnsLookup $dnsLookup, IpMatcher $ipMatcher, RequestInterface $request, ResponseInterface $response)
    {
        $dnsLookup->ip('example.com')->shouldBeCalled()->willReturn('192.168.1.42');
        $ipMatcher->match('192.168.1.42', ['192.168.1.54'])->shouldBeCalled()->willReturn(false);
        $this->shouldThrow(NotLoadableException::class)->during('checkRedirectIsValid', [
            $request,
            $response,
            new Uri('https://example.com/image.jpg')
        ]);
    }

    function it_allows_a_redirect_not_in_whitelist_or_private_range(DnsLookup $dnsLookup, IpMatcher $ipMatcher, RequestInterface $request, ResponseInterface $response)
    {
        $dnsLookup->ip('example.com')->shouldBeCalled()->willReturn('8.8.8.8');
        $ipMatcher->match('8.8.8.8', ['192.168.1.54'])->shouldBeCalled()->willReturn(false);
        $this->checkRedirectIsValid(
            $request,
            $response,
            new Uri('https://example.com/image.jpg')
        );
    }
}
