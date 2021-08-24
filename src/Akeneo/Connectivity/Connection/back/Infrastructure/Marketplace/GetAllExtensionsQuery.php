<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllExtensionsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllExtensionsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use Akeneo\Platform\VersionProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllExtensionsQuery implements GetAllExtensionsQueryInterface
{
    private const MAX_REQUESTS = 10;

    private WebMarketplaceApiInterface $webMarketplaceApi;
    private int $pagination;

    public function __construct(
        WebMarketplaceApiInterface $webMarketplaceApi,
        int $pagination
    ) {
        $this->webMarketplaceApi = $webMarketplaceApi;
        $this->pagination = $pagination;
    }

    public function execute(): GetAllExtensionsResult
    {
        $extensions = [];
        $requests = 0;
        $offset = 0;

        do {
            $result = $this->webMarketplaceApi->getExtensions($offset, $this->pagination);
            $requests++;
            $offset += $result['limit'];

            foreach ($result['items'] as $item) {
                $extensions[] = Extension::fromWebMarketplaceValues($item);
            }
        } while (count($result['items']) > 0 && count($extensions) < $result['total'] && $requests < self::MAX_REQUESTS);

        return GetAllExtensionsResult::create($result['total'], $extensions);
    }
}
