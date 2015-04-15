<?php

namespace Pim\Bundle\DataGridBundle\Adapter;

use Symfony\Component\HttpFoundation\Request;

/**
 * Transform Oro filters into Akeneo PIM filters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GridFilterAdapterInterface
{
    /**
     * @param Request $request
     *
     * @return array
     *
     * Converter is also eligible
     */
    public function adapt(Request $request);
}
