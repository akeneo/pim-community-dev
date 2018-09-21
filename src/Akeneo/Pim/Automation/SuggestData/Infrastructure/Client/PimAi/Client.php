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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class Client
{
    /** @var ClientInterface */
    private $httpClient;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var string */
    private $token;

    /**
     * @param ClientInterface $httpClient
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(ClientInterface $httpClient, ConfigurationRepositoryInterface $configurationRepository)
    {
        $this->httpClient = $httpClient;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Send request to PIM.ai.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $method, string $uri, array $options = [])
    {
        $options = $options + [
            'headers' => ['Authorization' => $this->getToken()],
        ];

        return $this->httpClient->request($method, $uri, $options);
    }

    /**
     * @return null|string
     */
    private function getToken(): ?string
    {
        if (empty($this->token)) {
            $config = $this->configurationRepository->find();
            if ($config instanceof Configuration) {
                $this->token = $config->getToken();
            }
        }

        return $this->token;
    }
}
