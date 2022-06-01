<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Connectivity\Connection\Infrastructure\Marketplace;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;

class WebMarketplaceAliases implements WebMarketplaceAliasesInterface
{
    private WebMarketplaceAliasesInterface $decorated;
    private VersionProviderInterface $versionProvider;

    public function __construct(
        WebMarketplaceAliasesInterface $decorated,
        VersionProviderInterface $versionProvider
    ) {
        $this->decorated = $decorated;
        $this->versionProvider = $versionProvider;
    }

    public function getUtmCampaign(): ?string
    {
        switch ($this->versionProvider->getEdition()) {
            case 'Serenity':
                return 'connect_serenity';
            default:
                return null;
        }
    }

    public function getEdition(): string
    {
        switch ($this->versionProvider->getEdition()) {
            case 'Serenity':
                return 'serenity';
            case 'EE':
            default:
                return 'enterprise-edition';
        }
    }

    public function getVersion(): ?string
    {
        return $this->decorated->getVersion();
    }
}
