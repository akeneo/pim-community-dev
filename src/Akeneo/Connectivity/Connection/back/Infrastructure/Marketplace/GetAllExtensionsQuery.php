<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\Extension;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\ExtensionResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllExtensionsQueryInterface;
use Akeneo\Platform\VersionProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllExtensionsQuery implements GetAllExtensionsQueryInterface
{
    private WebMarketplaceApi $webMarketplaceApi;
    private VersionProviderInterface $versionProvider;

    public function __construct(WebMarketplaceApi $webMarketplaceApi, VersionProviderInterface $versionProvider)
    {
        $this->webMarketplaceApi = $webMarketplaceApi;
        $this->versionProvider = $versionProvider;
    }

    public function execute(): ExtensionResult
    {
        $version = $this->versionProvider->getVersion();
        $edition = $this->versionProvider->getEdition();

        $result = $this->webMarketplaceApi->getExtensions($edition, $version);

        $extensions = [];

        foreach ($result['items'] as $item) {
            $extensions[] = Extension::create($item);
        }

        return ExtensionResult::create($result['total'], $extensions);
    }
}
