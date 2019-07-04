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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;

/**
 * Checks if a suggest data connection is active or not.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetConnectionStatusHandler
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var AuthenticationProviderInterface */
    private $authenticationProvider;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var ConnectionStatus */
    private $connectionStatus;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param AuthenticationProviderInterface $authenticationProvider
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        AuthenticationProviderInterface $authenticationProvider,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->authenticationProvider = $authenticationProvider;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
    }

    /**
     * @param GetConnectionStatusQuery $query
     *
     * @return ConnectionStatus
     */
    public function handle(GetConnectionStatusQuery $query): ConnectionStatus
    {
        if (null === $this->connectionStatus) {
            $identifiersMapping = $this->identifiersMappingRepository->find();
            $configuration = $this->configurationRepository->find();
            $productSubscriptionCount = $this->productSubscriptionRepository->count();

            $isActive = $configuration->getToken() instanceof Token;

            $isValid = false;
            if ($query->checkTokenValidity() && true === $isActive) {
                $isValid = $this->authenticationProvider->authenticate($configuration->getToken());
            }

            $this->connectionStatus = new ConnectionStatus(
                $isActive,
                $isValid,
                $identifiersMapping->isValid(),
                $productSubscriptionCount
            );
        }

        return $this->connectionStatus;
    }

    public function clearCache(): void
    {
        $this->connectionStatus = null;
    }
}
