<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderAdapterInterface;

/**
 * Registry for data provider adapters
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DataProviderAdapterRegistry
{
    /** @var DataProviderAdapterInterface[] */
    private $dataProviderAdapters = [];

    /**
     * @param string $adapterKey
     * @param DataProviderAdapterInterface $dataProviderAdapter
     */
    public function addAdapter(string $adapterKey, DataProviderAdapterInterface $dataProviderAdapter): void
    {
        $this->dataProviderAdapters[$adapterKey] = $dataProviderAdapter;
    }

    /**
     * @param string $adapterKey
     *
     * @return DataProviderAdapterInterface
     *
     * @throws \Exception
     */
    public function getAdapter(string $adapterKey)
    {
        if (!isset($this->dataProviderAdapters[$adapterKey])) {
            throw new \Exception(sprintf('Adapter "%s" not found', $adapterKey));
        }

        return $this->dataProviderAdapters[$adapterKey];
    }
}
