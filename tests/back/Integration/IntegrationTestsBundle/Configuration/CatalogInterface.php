<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Configuration;

use Akeneo\Test\Integration\Configuration;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CatalogInterface
{
    /**
     * @deprecated please do not use it as SQL is not maintainable at all
     *
     * @return Configuration
     */
    public function useTechnicalSqlCatalog(): Configuration;

    /**
     * @return Configuration
     */
    public function useTechnicalCatalog(): Configuration;

    /**
     * @return Configuration
     */
    public function useMinimalCatalog(): Configuration;

    /**
     * Returns the path to a given functional (aka behat) catalog.
     *
     * @param string $catalog
     *
     * @return Configuration
     */
    public function useFunctionalCatalog(string $catalog): Configuration;
}
