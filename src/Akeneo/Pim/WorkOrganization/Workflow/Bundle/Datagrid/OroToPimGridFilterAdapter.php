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

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\Filter\ProjectCompletenessFilter;
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
        if (in_array($parameters['gridName'], [self::PRODUCT_GRID_NAME, self::PUBLISHED_PRODUCT_GRID_NAME])) {
            $filters = $this->massActionDispatcher->getRawFilters($parameters);

            //It is project view from grid
            if (isset($parameters['filters']['project_completeness']) && !$this->containsFilters($filters, ['id', 'sku'])) {
                $filters = array_merge(
                    $filters,
                    $this->getCompletenessForProjectFilter(
                        (int) $parameters['filters']['project_completeness']['value'],
                        $parameters['dataLocale'],
                        $parameters['dataScope']['value']
                    )
                );
            }

            return $filters;
        }

        if ($parameters['gridName'] === self::APPROVE_GRID_NAME) {
            return ['values' => $this->massActionDispatcher->dispatch($parameters)];
        }

        return $this->adaptDefaultGrid($parameters);
    }

    private function getCompletenessForProjectFilter(int $projectCompleteness, string $locale, string $channel): array
    {
        $completenessFilter = function (string $operator, int $value) use ($locale, $channel): array {
            return [
                'field' => 'completeness',
                'operator' => $operator,
                'value' => $value,
                'context' => [
                    'locale' => $locale,
                    'scope' => $channel
                ]
            ];
        };

        switch ($projectCompleteness) {
            case ProjectCompletenessFilter::CONTRIBUTOR_TODO:
            case ProjectCompletenessFilter::OWNER_TODO:
                return [$completenessFilter('=', 0)];
            case ProjectCompletenessFilter::CONTRIBUTOR_IN_PROGRESS:
            case ProjectCompletenessFilter::OWNER_IN_PROGRESS:
                return [$completenessFilter('>', 0), $completenessFilter('<', 100)];
            case ProjectCompletenessFilter::CONTRIBUTOR_DONE:
            case ProjectCompletenessFilter::OWNER_DONE:
                return [$completenessFilter('=', 100)];
            default:
                return [];
        }
    }

    private function containsFilters(array $filters, array $fieldNames): bool
    {
        foreach ($filters as $filter) {
            if (in_array($filter['field'], $fieldNames)) {
                return true;
            }
        }
        return false;
    }
}
