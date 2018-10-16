<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid;

use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter as BaseAdapter;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;

/**
 * Transform Oro filters into Akeneo PIM filters
 *
 * @author Julien Janvier <j.janvier@gmail.com>
 */
class OroToPimGridFilterAdapter extends BaseAdapter
{
    private const APPROVE_GRID_NAME = 'proposal-grid';
    private const PUBLISHED_PRODUCT_GRID_NAME = 'published-product-grid';

    /**
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(MassActionDispatcher $massActionDispatcher)
    {
        parent::__construct($massActionDispatcher);
    }

    /**
     * {@inheritdoc}
     */
    public function adapt(array $parameters)
    {
        if ($parameters['gridName'] === self::PUBLISHED_PRODUCT_GRID_NAME) {
            return $this->massActionDispatcher->getRawFilters($parameters);
        }

        if ($parameters['gridName'] === self::APPROVE_GRID_NAME) {
            return ['values' => $this->massActionDispatcher->dispatch($parameters)];
        }
    }
}
