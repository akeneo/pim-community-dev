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

namespace Akeneo\Pim\Automation\SuggestData\Application\Configuration\Validator;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;

/**
 * Validate that the provided token is valid.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ConnectionValidator
{
    /** @var DataProviderInterface */
    private $dataProvider;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->dataProvider = $dataProviderFactory->create();
        $this->configurationRepository = $configurationRepository;
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
        return $this->dataProvider->authenticate($token);
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
