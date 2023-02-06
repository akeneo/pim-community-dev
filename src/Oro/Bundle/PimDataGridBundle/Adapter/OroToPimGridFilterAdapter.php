<?php

namespace Oro\Bundle\PimDataGridBundle\Adapter;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;

/**
 * Transform Oro filters into Akeneo PIM filters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OroToPimGridFilterAdapter implements GridFilterAdapterInterface
{
    const FAMILY_GRID_NAME = 'family-grid';

    const PRODUCT_GRID_NAME = 'product-grid';

    const ATTRIBUTE_GRID_NAME = 'attribute-grid';

    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /**
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(MassActionDispatcher $massActionDispatcher)
    {
        $this->massActionDispatcher = $massActionDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function adapt(array $parameters)
    {
        return match ($parameters['gridName']) {
            self::PRODUCT_GRID_NAME => $this->massActionDispatcher->getRawFilters($parameters),
            self::ATTRIBUTE_GRID_NAME => $this->adaptAttributeGrid($parameters),
            default => $this->adaptDefaultGrid($parameters)
        };
    }

    /**
     * Adapt filters for the default grids
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function adaptDefaultGrid(array $parameters): array
    {
        if (isset($parameters['inset']) && true === $parameters['inset']) {
            $parameters['filters'] = [];
        }

        $items = $this->massActionDispatcher->dispatch($parameters);

        foreach ($items as &$object) {
            if (is_array($object)) {
                $object = $object[0];
            }
        }

        $itemIds = [];

        foreach ($items as $item) {
            $itemIds[] = $item->getId();
        }

        return [
            ['field' => 'id', 'operator' => 'IN', 'value' => $itemIds]
        ];
    }

    protected function adaptAttributeGrid(array $parameters): array
    {
        return [
            'field' => 'code',
            'operator' => $parameters['inset'] ? 'IN' : 'NOT IN',
            'values' => $parameters['values']
        ];
    }
}
