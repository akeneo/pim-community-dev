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

use Akeneo\AssetManager\Infrastructure\Network\UrlChecker;
use GuzzleHttp\Client;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * A redirect URL can target an unauthorized protocol (ftp, etc).
 * Therefore, it's mandatory to check redirection at runtime when getting the content of a media link.
 *
 * The original implementation of this loader in the library uses `file_get_contents` to fetch content.
 * The issue with this method is that there is no extension point to check the validity of an URL redirection,
 * hence the replacement of the library implementation by this one, with Guzzle.
 */
final class RejectInvalidRedirectionStreamLoader implements LoaderInterface
{
    public function __construct(private UrlChecker $urlChecker)
    {
    }

    public function find($path)
    {
        $client = new Client([
            'timeout' => 2,
            'headers' => ['User-Agent' => null],
            'allow_redirects' => [
                'max' => 10,
                'strict' => true,
                'referer' => true,
                'protocols' => $this->urlChecker->getAllowedProtocols(),
                'on_redirect' => fn (RequestInterface $request, ResponseInterface $response, UriInterface $uri): bool => $this->checkRedirectIsValid($request, $response, $uri),
                'track_redirects' => true,
            ],
        ]);

        $response = $client->get($path);

        return $response->getBody()->getContents();
    }

    public function checkRedirectIsValid(
        RequestInterface $request,
        ResponseInterface $response,
        UriInterface $uri
    ): bool {
        if (!$this->urlChecker->isProtocolAllowed($uri->getScheme())) {
            throw new NotLoadableException('The provided link redirects to an invalid URL scheme.');
        }

        if (!$this->urlChecker->isDomainAllowed($uri->getHost())) {
            throw new NotLoadableException('The provided link redirects to an unallowed domain.');
        }

        return true;
    }
}
