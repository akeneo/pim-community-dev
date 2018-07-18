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

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderAdapterInterface;

/**
 * Registry for data providers
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DataProviderRegistry
{
    /** @var DataProviderAdapterInterface[] */
    private $dataProviders = [];

    /**
     * @param string $alias
     * @param DataProviderAdapterInterface $dataProvider
     */
    public function addDataProvider(string $alias, DataProviderAdapterInterface $dataProvider): void
    {
        $this->dataProviders[$alias] = $dataProvider;
    }

    /**
     * @param string $alias
     *
     * @return DataProviderAdapterInterface
     *
     * @throws \Exception
     */
    public function getDataProvider(string $alias): DataProviderAdapterInterface
    {
        if (!isset($this->dataProviders[$alias])) {
            throw new \Exception(sprintf('Data provider "%s" not found', $alias));
        }

        return $this->dataProviders[$alias];
    }
}
