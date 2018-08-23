<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductFetch\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FetchProductsHandler
{
    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /**
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(DataProviderFactory $dataProviderFactory)
    {
        $this->dataProviderFactory = $dataProviderFactory;
    }

    /**
     * @param FetchProductsCommand $command
     */
    public function handle(FetchProductsCommand $command): void
    {
        // TODO: Calculate last date (from command or fetch from repository)

        // TODO: Deal with many pages
        $dataProvider = $this->dataProviderFactory->create();
        $subscriptions = $dataProvider->fetch();

        // TODO: Store fetched data in DB




        /**
         * GET /api/subscriptions/updated-since/yesterday
        [
            {
                upc: "upc1",
                attributes: {},
                "missing_mapping": true,
                unmapped_attributes: {…}
            }
        ]
         */

    }
}
