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
use GuzzleHttp;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class RejectInvalidRedirectionStreamLoader implements LoaderInterface
{
    private UrlChecker $urlChecker;

    public function __construct(UrlChecker $urlChecker)
    {
        $this->urlChecker = $urlChecker;
    }

    public function find($path)
    {
        $client = new GuzzleHttp\Client([
            'allow_redirects' => [
                'max' => 10,
                'strict'          => true,
                'referer'         => true,
                'protocols'       => $this->urlChecker->getAllowedProtocols(),
                'on_redirect'     => array($this, 'checkRedirectIsValid'),
                'track_redirects' => true,
            ],
        ]);

        $response = $client->get($path);

        return $response->getBody()->getContents();
    }

    public function checkRedirectIsValid(RequestInterface $request, ResponseInterface $response, UriInterface $uri): bool
    {
        if (!$this->urlChecker->isProtocolAllowed($uri->getScheme())) {
            throw new NotLoadableException('The provided link redirects to an invalid URL scheme.');
        }

        if (!$this->urlChecker->isDomainAllowed($uri->getHost())) {
            throw new NotLoadableException('The provided link redirects to a unallowed domain.');
        }

        return true;
    }
}
