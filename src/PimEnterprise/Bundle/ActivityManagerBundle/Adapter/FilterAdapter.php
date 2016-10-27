<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Adapter;

use Akeneo\ActivityManager\Component\Adapter\FilterAdapterInterface;
use Pim\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FilterAdapter implements FilterAdapterInterface
{
    /** @var OroToPimGridFilterAdapter */
    private $adapter;

    /**
     * @param OroToPimGridFilterAdapter $adapter
     */
    public function __construct(OroToPimGridFilterAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function adapt(Request $request, $filters)
    {
        $request->query->add(
            [
                'gridName'   => OroToPimGridFilterAdapter::PRODUCT_GRID_NAME,
                'actionName' => 'mass_edit', //Fake mass action, needed for the grid filter adapter.
                'inset'      => false,
                'filters'    => $filters,
            ]
        );

        return $this->adapter->adapt($request);
    }
}
