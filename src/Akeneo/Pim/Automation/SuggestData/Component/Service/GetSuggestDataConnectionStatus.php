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

namespace Akeneo\Pim\Automation\SuggestData\Component\Service;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;

/**
 * Checks if a suggest data connection is active or not.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSuggestDataConnectionStatus
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function forCode(string $code): bool
    {
        $configuration = $this->configurationRepository->findOneByCode($code);
        if (null === $configuration) {
            return false;
        }

        $dataProvider = $this->dataProviderFactory->create();

        return $dataProvider->authenticate($configuration->getToken());
    }
}
