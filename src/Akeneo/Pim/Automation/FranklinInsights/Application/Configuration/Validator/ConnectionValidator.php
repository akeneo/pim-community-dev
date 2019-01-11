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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Validator;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;

/**
 * Validate that the provided token is valid.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ConnectionValidator
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var AuthenticationProviderInterface */
    private $authenticationProvider;

    /**
     * @param AuthenticationProviderInterface $authenticationProvider
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        AuthenticationProviderInterface $authenticationProvider,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->authenticationProvider = $authenticationProvider;
    }

    /**
     * Validates the given token.
     *
     * @param Token $token
     *
     * @return bool
     */
    public function isTokenValid(Token $token)
    {
        return $this->authenticationProvider->authenticate($token);
    }

    /**
     * Validates the connection from the registered token.
     *
     * @return bool
     */
    public function isValid()
    {
        $configuration = $this->configurationRepository->find();

        return $this->isTokenValid($configuration->getToken());
    }
}
