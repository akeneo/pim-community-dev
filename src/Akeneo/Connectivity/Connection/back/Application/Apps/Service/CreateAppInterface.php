<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\App;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App as MarketplaceApp;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateAppInterface
{
    /**
     * Creates and persists a new App
     *
     * @param MarketplaceApp $marketplaceApp
     * @param string[] $scopes
     * @param string $connectionCode
     * @return App
     */
    public function execute(MarketplaceApp $marketplaceApp, array $scopes, string $connectionCode): App;
}
