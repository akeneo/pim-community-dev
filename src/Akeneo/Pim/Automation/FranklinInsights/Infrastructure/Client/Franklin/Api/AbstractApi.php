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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ClientInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\UriGenerator;
use Psr\Log\LoggerInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AbstractApi
{
    /** @var UriGenerator */
    protected $uriGenerator;

    /** @var ClientInterface */
    protected $httpClient;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param UriGenerator $uriGenerator
     * @param ClientInterface $httpClient
     */
    public function __construct(
        UriGenerator $uriGenerator,
        ClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->httpClient->setToken($token);
    }
}
