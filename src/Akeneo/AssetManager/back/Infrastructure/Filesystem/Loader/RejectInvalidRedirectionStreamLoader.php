<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Filesystem\Loader;

use Akeneo\AssetManager\Infrastructure\Network\DnsLookupInterface;
use Akeneo\AssetManager\Infrastructure\Network\IpMatcher;
use GuzzleHttp;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class RejectInvalidRedirectionStreamLoader implements LoaderInterface
{
    private array $allowedMediaLinksProtocols;
    private DnsLookupInterface $dnsLookup;
    private IpMatcher $ipMatcher;
    /** @var string[] */
    private array $networkWhitelist;

    private const DOMAIN_BLACKLIST = [
        'localhost',
        'elasticsearch',
        'memcached',
        'object-storage',
        'mysql',
    ];

    public function __construct(
        $allowedMediaLinksProtocols,
        DnsLookupInterface $dnsLookup,
        IpMatcher $ipMatcher,
        string $networkWhitelist = ''
    ) {
        $this->allowedMediaLinksProtocols = $allowedMediaLinksProtocols;
        $this->dnsLookup = $dnsLookup;
        $this->ipMatcher = $ipMatcher;
        $this->networkWhitelist = empty($networkWhitelist) ? [] : \explode(',', $networkWhitelist);
    }

    public function find($path)
    {
        $client = new GuzzleHttp\Client([
            'allow_redirects' => [
                'max' => 10,
                'strict'          => true,
                'referer'         => true,
                'protocols'       => $this->allowedMediaLinksProtocols,
                'on_redirect'     => array($this, 'checkRedirectIsValid'),
                'track_redirects' => true
            ]
        ]);

        $response = $client->get($path);

        return $response->getBody()->getContents();
    }

    public function checkRedirectIsValid(RequestInterface $request, ResponseInterface $response, UriInterface $uri): bool
    {
        if (!in_array($uri->getScheme(), $this->allowedMediaLinksProtocols)) {
            throw new NotLoadableException('The provided link redirects to an invalid URL scheme.');
        }

        $host = \strtolower($uri->getHost());
        if (\in_array($host, self::DOMAIN_BLACKLIST)) {
            throw new NotLoadableException('The provided link redirects to a blacklisted domain.');
        }

        $ip = $this->dnsLookup->ip($host);
        if (null === $ip) {
            return true;
        }

        if ($this->isInWhitelist($ip)) {
            return false;
        }

        if ($this->isInPrivateRange($ip)) {
            throw new NotLoadableException('The provided link redirects to an IP on the private range.');
        }

        return true;
    }

    private function isInPrivateRange(string $ip): bool
    {
        return !\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE);
    }

    private function isInWhitelist(string $ip): bool
    {
        if (empty($this->networkWhitelist)) {
            return false;
        }

        return $this->ipMatcher->match($ip, $this->networkWhitelist);
    }
}
