<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

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
