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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ClientInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\UriGenerator;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AbstractApi implements AuthenticatedApi
{
    /** @var UriGenerator */
    protected $uriGenerator;

    /** @var ClientInterface */
    protected $httpClient;

    /**
     * @param UriGenerator $uriGenerator
     * @param ClientInterface $httpClient
     */
    public function __construct(
        UriGenerator $uriGenerator,
        ClientInterface $httpClient
    ) {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->httpClient->setToken($token);
    }
}
