<?php

namespace Pim\Bundle\DataGridBundle\Adapter;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\Request;

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
    public function adapt(Request $request)
    {
        if (self::PRODUCT_GRID_NAME === $request->get('gridName')) {
            $filters = $this->massActionDispatcher->getRawFilters($request);
        } else {
            $items = $this->massActionDispatcher->dispatch($request);

            foreach ($items as &$object) {
                if (is_array($object)) {
                    $object = $object[0];
                }
            }

            $itemIds = [];

            foreach ($items as $item) {
                $itemIds[] = $item->getId();
            }

            $filters = [
                ['field' => 'id', 'operator' => 'IN', 'value' => $itemIds]
            ];
        }

        return $filters;
    }
}
