<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Adapter;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface FilterAdapterInterface
{
    /**
     * It adds values to the request as it's needed by the adapter to transform oro grid filters into PQB filter.
     *
     * @param Request $request
     * @param string  $filters
     *
     * @return array
     */
    public function adapt(Request $request, $filters);
}
