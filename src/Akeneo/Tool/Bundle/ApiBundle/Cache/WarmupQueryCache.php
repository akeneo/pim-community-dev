<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Cache;

use Symfony\Component\HttpFoundation\Request;

/**
 * Warms up LRU cached queries in order to minimize the number of SQL queries performed towards the DB.
 * This is particularly useful when dealing with entities one by one (such as in the product/product model upsert list
 * endpoints: early analyzis of the initial request allows to gather the queries' arguments (e.g attribute codes)
 * and store the results in the LRU cache
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WarmupQueryCache
{
    public function fromRequest(Request $request): void;
}
