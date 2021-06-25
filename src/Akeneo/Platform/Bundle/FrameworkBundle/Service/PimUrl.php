<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\Service;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUrl
{
    private string $pimUrl;

    public function __construct(string $pimUrl)
    {
        $this->pimUrl = $pimUrl;
    }

    public function getPimUrl():string
    {
        return $this->pimUrl;
    }
}
