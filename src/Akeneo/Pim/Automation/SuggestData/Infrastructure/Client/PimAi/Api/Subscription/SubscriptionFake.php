<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;

final class SubscriptionFake implements SubscriptionApiInterface
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var AuthenticationApiInterface */
    private $authenticationApi;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param AuthenticationApiInterface $authenticationApi
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        AuthenticationApiInterface $authenticationApi
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->authenticationApi = $authenticationApi;
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(array $identifiers): ApiResponse
    {
        $this->authenticate();

        $filename = sprintf('subscribe-%s-%s.json', key($identifiers), current($identifiers));

        return new ApiResponse(
            200,
            new SubscriptionCollection(
                json_decode(
                    file_get_contents(
                        sprintf(__DIR__ . '/../resources/%s', $filename)
                    ),
                    true
                )
            )
        );
    }

    /**
     * @throws InvalidTokenException
     */
    private function authenticate(): void
    {
        $token = $this->configurationRepository->findOneByCode('pim-ai')->getToken();
        if (true !== $this->authenticationApi->authenticate($token)) {
            throw new InvalidTokenException('The pim.ai token is missing or invalid');
        }
    }
}
