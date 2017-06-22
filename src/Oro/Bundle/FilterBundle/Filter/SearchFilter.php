<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Pim\Component\Catalog\Query\Filter\Operators;

class SearchFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
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
            $ds->setParameter($parameterName, $word);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormType()
    {
        return TextFilterType::class;
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !$data['value']) {
            return [];
        }

        return array_map(function ($word) {
            return sprintf('%%%s%%', $word);
        }, preg_split('/\s+/', $words = $data['value']));
    }
}
