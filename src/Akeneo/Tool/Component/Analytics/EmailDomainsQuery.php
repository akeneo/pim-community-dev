<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Analytics;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EmailDomainsQuery
{
    public function fetch(): string;
}
