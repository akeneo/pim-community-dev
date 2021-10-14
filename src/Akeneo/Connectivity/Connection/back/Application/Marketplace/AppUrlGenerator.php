<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AppUrlGenerator
{
    private PimUrl $pimUrl;

    public function __construct(PimUrl $pimUrl)
    {
        $this->pimUrl = $pimUrl;
    }

    /**
     * @return array<string, string>
     */
    public function getAppQueryParameters(): array
    {
        return ['pim_url' => $this->pimUrl->getPimUrl()];
    }
}
