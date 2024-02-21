<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Cache;

use Symfony\Component\HttpFoundation\Request;

/**
 * When importing product/product model, a lot of Database queries are triggered. We suffer from the (1+n) queries issue,
 * which drastically impacts the performance.
 * @see https://www.sitepoint.com/silver-bullet-n1-problem/
 *
 * One way to avoid that is to pre-load the needed data in 1 query instead of 1+n queries, via early request analysis.
 * This trick drastically increases the performances, though there are two drawbacks:
 *
 * - it means we know that subrequests are dispatched in sub-layers, meaning that we have knowledge of an internal behavior.
 * Therefore, changing the implementation of the internal behavior can make this cache warmup obsolete.
 *
 * - our abstraction to import products is leaky: https://www.joelonsoftware.com/2002/11/11/the-law-of-leaky-abstractions/
 * The queries are cached, and therefore stateful. Do note that this was already the case even before this interface, which mitigates this drawback
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WarmupQueryCache
{
    public function fromRequest(Request $request): void;
}
