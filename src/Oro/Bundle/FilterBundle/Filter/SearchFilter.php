<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;

class SearchFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool
    {
        $data = $this->parseData($data);
        if (0 === count($data)) {
            return false;
        }

        foreach ($data as $word) {
            $parameterName = $ds->generateParameterName($this->getName());
            $this->applyFilterToClause(
                $ds,
                $ds->expr()->comparison(
                    $this->get(FilterUtility::DATA_NAME_KEY),
                    Operators::IS_LIKE,
                    $parameterName,
                    true
                )
            );
            $ds->setParameter($parameterName, addcslashes($word, '_'));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormType(): string
    {
        return TextFilterType::class;
    }

    /**
     * @param mixed $data
     */
    protected function parseData($data): array
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !$data['value']) {
            return [];
        }

        return array_map(fn($word) => sprintf('%%%s%%', $word), preg_split('/\s+/', $words = $data['value']));
    }
}
