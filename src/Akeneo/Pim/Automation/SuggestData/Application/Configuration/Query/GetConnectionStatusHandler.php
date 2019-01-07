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

namespace Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;

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
     * @return ConnectionStatus
     */
    public function handle(GetConnectionStatusQuery $query): ConnectionStatus
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        $configuration = $this->configurationRepository->find();
        $productSubscriptionCount = $this->productSubscriptionRepository->count();
        $isActive = false;
        if ($configuration->getToken() instanceof Token) {
            $isActive = $this->authenticationProvider->authenticate($configuration->getToken());
        }

        return new ConnectionStatus(
            $isActive,
            $identifiersMapping->isValid(),
            $productSubscriptionCount
        );
    }
}
