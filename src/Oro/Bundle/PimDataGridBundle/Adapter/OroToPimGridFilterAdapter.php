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
        protected MassActionDispatcher $massActionDispatcher,
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
        if (true === $parameters['inset']) {
            return [
                'search' => null,
                'options' => [
                    'identifiers' => $parameters['values'],
                ],
            ];
        }

        $filters = $parameters['filters'];

        return [
            'search' => $filters['label']['value'] ?? null,
            'options' => [
                'excluded_identifiers' => $parameters['values'],
                'code' => !empty($filters['code']) ? $filters['code'] : null,
                'types' => $this->adaptArrayFilter($filters['type']['value'] ?? [], null),
                'attribute_groups' =>  $this->adaptArrayFilter($filters['group']['value'] ?? [], []),
                'scopable' => $this->adaptTrileanFilter($filters['scopable']['value'] ?? null),
                'localizable' => $this->adaptTrileanFilter($filters['localizable']['value'] ?? null),
                'families' => $this->adaptArrayFilter($filters['family']['value'] ?? [], null),
                'smart' => $this->adaptTrileanFilter($filters['smart']['value'] ?? null),
                'quality' => $filters['quality']['value'] ?? null,
            ],
        ];
    }

    private function adaptArrayFilter(array $value, ?array $fallback): ?array
    {
        $filteredValue = array_filter($value);

        return empty($filteredValue) ? $fallback : $filteredValue;
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
