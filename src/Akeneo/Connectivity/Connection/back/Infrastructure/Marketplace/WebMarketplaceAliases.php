<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceAliases implements WebMarketplaceAliasesInterface
{
    public function __construct(
        private VersionProviderInterface $versionProvider,
    ) {
    }

    public function getUtmCampaign(): ?string
    {
        return null;
    }

    public function getEdition(): string
    {
        return 'community-edition';
    }

    public function getVersion(): ?string
    {
        $version = $this->versionProvider->getVersion();

        if (\preg_match('|(\d\.\d)\.\d|', $version, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
