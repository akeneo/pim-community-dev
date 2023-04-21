<?php

namespace Akeneo\Pim\Structure\Bundle\Datagrid\Attribute;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\AbstractFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface as PimFilterDatasourceAdapterInterface;

final class AttributeFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool
    {
        if (!$ds instanceof PimFilterDatasourceAdapterInterface) {
            return false;
        }

        $data = $this->parseData($data);
        if (0 === count($data)) {
            return false;
        }

        $rootAlias = current($ds->getQueryBuilder()->getRootAliases());
        foreach ($data as $word) {
            $parameterName = $ds->generateParameterName($this->getName());
            $this->applyFilterToClause(
                $ds,
                $ds->expr()->orX(
                    $ds->expr()->comparison(
                        "$rootAlias.code",
                        'LIKE',
                        $parameterName,
                        true
                    ),
                    $ds->expr()->comparison(
                        'translation.label',
                        'LIKE',
                        $parameterName,
                        true
                    )
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
     */
    protected function parseData($data): array
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !$data['value']) {
            return [];
        }

        return array_map(function ($word) {
            return sprintf('%%%s%%', \addcslashes($word, '_%'));
        }, preg_split('/\s+/', $data['value']));
    }
}
