<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin;

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface ClientInterface
{
    /**
     * Send request to Franklin.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @throws BadResponseException
     *
     * @return ResponseInterface
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface;

    /**
     * @param string $token
     */
    public function setToken(string $token): void;
}
