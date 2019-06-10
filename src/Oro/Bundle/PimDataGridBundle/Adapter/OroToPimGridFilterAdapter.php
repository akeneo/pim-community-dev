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
        if (self::PRODUCT_GRID_NAME === $parameters['gridName']) {
            $filters = $this->massActionDispatcher->getRawFilters($parameters);
        } else {
            $filters = $this->adaptDefaultGrid($parameters);
        }

        return $filters;
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
}
