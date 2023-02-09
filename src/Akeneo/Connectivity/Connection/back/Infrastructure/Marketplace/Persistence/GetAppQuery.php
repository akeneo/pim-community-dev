<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAppQuery implements GetAppQueryInterface
{
    public function __construct(
        private readonly WebMarketplaceApiInterface $webMarketplaceApi,
        private readonly GetCustomAppQuery $getCustomAppQuery,
    ) {
    }

    public function execute(string $id): ?App
    {
        $data = $this->getCustomAppQuery->execute($id);

        if (null !== $data) {
            return App::fromCustomAppValues($data);
        }

        $data = $this->webMarketplaceApi->getApp($id);

        if (null === $data) {
            return null;
        }

        return App::fromWebMarketplaceValues($data);
    }
}
