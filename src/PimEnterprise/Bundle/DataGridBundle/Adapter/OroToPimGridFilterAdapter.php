<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Adapter;

use Pim\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter as BaseAdapter;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Transform Oro filters into Akeneo PIM filters
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class OroToPimGridFilterAdapter extends BaseAdapter
{
    private const APPROVE_GRID_NAME = 'proposal-grid';

    private const PUBLISHED_PRODUCT_GRID_NAME = 'published-product-grid';

    private const RULE_GRID_NAME = 'rule-grid';

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
        if (in_array($parameters['gridName'], [self::PRODUCT_GRID_NAME, self::PUBLISHED_PRODUCT_GRID_NAME])) {
            $filters = $this->massActionDispatcher->getRawFilters($parameters);
        } elseif (in_array($parameters['gridName'], [self::APPROVE_GRID_NAME, self::RULE_GRID_NAME])) {
            return ['values' => $this->massActionDispatcher->dispatch($parameters)];
        } else {
            $filters = $this->adaptDefaultGrid($parameters);
        }

        return $filters;
    }
}
