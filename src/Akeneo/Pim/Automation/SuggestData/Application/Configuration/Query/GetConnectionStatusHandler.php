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
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;

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

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param AuthenticationProviderInterface $authenticationProvider
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        AuthenticationProviderInterface $authenticationProvider,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * @return ConnectionStatus
     */
    public function handle(GetConnectionStatusQuery $query): ConnectionStatus
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        $configuration = $this->configurationRepository->find();
        if (!$configuration->getToken() instanceof Token) {
            return new ConnectionStatus(false, $identifiersMapping->isValid());
        }
        $isActive = $this->authenticationProvider->authenticate($configuration->getToken());

        return new ConnectionStatus($isActive, $identifiersMapping->isValid());
    }
}
