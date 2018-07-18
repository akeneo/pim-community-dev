<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderAdapterInterface;

/**
 * Data provider factory
 * Creates the right adapter depending of the data provider used
 * and configures it
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class DataProviderFactory
{
    private $memoryDataProvider;

    private $pimAiDataProvider;

    private $environment;

    public function __construct(DataProviderAdapterInterface $memoryDataProvider, DataProviderAdapterInterface $pimAiDataProvider, string $environment)
    {
        $this->memoryDataProvider = $memoryDataProvider;
        $this->pimAiDataProvider = $pimAiDataProvider;
        $this->environment = $environment;
    }

    /**
     * @return DataProviderAdapterInterface
     */
    public function create(): DataProviderAdapterInterface
    {
        //Will be refactored
        if ($this->environment === 'prod') {
            return $this->pimAiDataProvider;
        }

        return $this->memoryDataProvider;
    }
}
