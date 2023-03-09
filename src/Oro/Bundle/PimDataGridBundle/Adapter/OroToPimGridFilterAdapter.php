<?php

declare(strict_types=1);

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
    public const PRODUCT_GRID_NAME = 'product-grid';
    public const ATTRIBUTE_GRID_NAME = 'attribute-grid';

    public function __construct(
        private MassActionDispatcher $massActionDispatcher,
    ) {
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
        $filters = $parameters['filters'];

        return [
            'search' => $filters['label']['value'] ?? null,
            'options' => [
                // TODO code filter type: 1 -> contains, type 2 -> does not contain, etc...
                'types' => $filters['type']['value'] ?? null,
                'attribute_groups' => $filters['group']['value'] ?? [],
                'scopable' => $this->adaptTrileanFilter($filters['scopable']['value'] ?? null),
                'localizable' => $this->adaptTrileanFilter($filters['localizable']['value'] ?? null),
                'families' => $filters['family']['value'] ?? null,
                // 'smart' => $this->adaptTrileanFilter($filters['smart']['value'] ?? null), // Not available in CE repo and not implemented in EE...
                // 'quality' => ?
            ],
        ];
    }

    private function adaptTrileanFilter(?string $value): ?bool
    {
        return match ($value) {
            '1' => true,
            '2' => false,
            default => null,
        };
    }
}
