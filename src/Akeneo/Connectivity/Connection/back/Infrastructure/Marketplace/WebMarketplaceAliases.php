<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Platform\VersionProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceAliases implements WebMarketplaceAliasesInterface
{
    private VersionProviderInterface $versionProvider;

    public function __construct(
        VersionProviderInterface $versionProvider
    ) {
        $this->versionProvider = $versionProvider;
    }

    public function getUtmCampaign(): ?string
    {
        switch ($this->versionProvider->getEdition()) {
            case 'GE':
                return 'connect_ge';
            default:
                return null;
        }
    }

    public function getEdition(): string
    {
        switch ($this->versionProvider->getEdition()) {
            case 'GE':
                return 'growth-edition';
            case 'CE':
            default:
                return 'community-edition';
        }
    }

    public function getVersion(): ?string
    {
        $version = $this->versionProvider->getVersion();

        if (preg_match('|(\d\.\d)\.\d|', $version, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
