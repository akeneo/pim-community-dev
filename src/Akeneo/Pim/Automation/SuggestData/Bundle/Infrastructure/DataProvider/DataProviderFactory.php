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
 * Data provider factory
 * Creates the right adapter depending of the data provider used
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DataProviderFactory
{
    /** @var DataProviderRegistry */
    private $dataProviderRegistry;

    /** @var string */
    private $dataProviderAlias;

    /**
     * @param DataProviderRegistry $dataProviderRegistry
     * @param string $dataProviderAlias
     */
    public function __construct(DataProviderRegistry $dataProviderRegistry, string $dataProviderAlias)
    {
        $this->dataProviderRegistry = $dataProviderRegistry;
        $this->dataProviderAlias = $dataProviderAlias;
    }

    /**
     * @return DataProviderAdapterInterface
     */
    public function create(): DataProviderAdapterInterface
    {
        return $this->dataProviderRegistry->getDataProvider($this->dataProviderAlias);
    }
}
