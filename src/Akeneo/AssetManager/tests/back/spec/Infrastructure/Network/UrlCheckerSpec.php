<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Network;

use Akeneo\AssetManager\Infrastructure\Network\DnsLookup;
use Akeneo\AssetManager\Infrastructure\Network\IpMatcher;
use PhpSpec\ObjectBehavior;

class UrlCheckerSpec extends ObjectBehavior
{
    function let(
        DnsLookup $dnsLookup,
        IpMatcher $ipMatcher
    ) {
        $this->beConstructedWith(
            ['http', 'https'],
            $dnsLookup,
            $ipMatcher,
            '192.168.1.54,192.168.0.2',
        );
    }

    function it_returns_allowed_protocols(): void
    {
        $this->getAllowedProtocols()->shouldReturn(['http', 'https']);
    }

    function it_tells_if_protocol_is_allowed(): void
    {
        $this->isProtocolAllowed('http')->shouldReturn(true);
        $this->isProtocolAllowed('HTTP')->shouldReturn(true);
        $this->isProtocolAllowed('https')->shouldReturn(true);
        $this->isProtocolAllowed('hTtPs')->shouldReturn(true);
        $this->isProtocolAllowed('ftp')->shouldReturn(false);
        $this->isProtocolAllowed('xxx')->shouldReturn(false);
        $this->isProtocolAllowed('')->shouldReturn(false);
    }

    function it_allows_when_ip_is_whitelisted(
        DnsLookup $dnsLookup,
        IpMatcher $ipMatcher
    ): void {
        $dnsLookup->ip('example.com')->shouldBeCalled()->willReturn('192.168.1.54');
        $ipMatcher->match('192.168.1.54', ['192.168.1.54', '192.168.0.2'])->shouldBeCalled()->willReturn(true);

        $this->isDomainAllowed('example.com')->shouldReturn(true);
    }

    function it_allows_when_ip_is_not_in_whitelist_or_private_range(
        DnsLookup $dnsLookup,
        IpMatcher $ipMatcher
    ) {
        $dnsLookup->ip('example.com')->shouldBeCalled()->willReturn('8.8.8.8');
        $ipMatcher->match('8.8.8.8', ['192.168.1.54', '192.168.0.2'])->shouldBeCalled()->willReturn(false);

        $this->isDomainAllowed('example.com')->shouldReturn(true);
    }

    function it_tells_when_domain_is_blacklisted()
    {
        $this->isDomainAllowed('localhost')->shouldReturn(false);
        $this->isDomainAllowed('elasticsearch')->shouldReturn(false);
        $this->isDomainAllowed('memcached')->shouldReturn(false);
        $this->isDomainAllowed('object-storage')->shouldReturn(false);
        $this->isDomainAllowed('mysql')->shouldReturn(false);
        $this->isDomainAllowed('example')->shouldReturn(true);
    }
}
