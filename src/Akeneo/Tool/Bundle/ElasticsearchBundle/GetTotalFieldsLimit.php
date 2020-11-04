<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

/**
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetTotalFieldsLimit
{
    private int $configurationLimit;

    public function __construct(int $configurationLimit)
    {
        $this->configurationLimit = $configurationLimit;
    }

    public function getLimit(): int
    {
        return $this->configurationLimit;
    }
}
