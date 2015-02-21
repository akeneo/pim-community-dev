<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;

/**
 * Product family filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter extends AjaxChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $dataSource, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        if (Operators::IS_EMPTY === strtoupper($data['type'])) {
            $this->util->applyFilter($dataSource, 'family.id', Operators::IS_EMPTY, null);
        } else {
            $this->util->applyFilter($dataSource, 'family.id', Operators::IN_LIST, $data['value']);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $metadata['emptyChoice'] = true;
        $metadata[FilterUtility::TYPE_KEY] = 'select2-choice';

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseData($data)
    {
        $data = parent::parseData($data);
        if (is_array($data['value'])) {
            $data['value'] = array_map('intval', $data['value']);
        }

        return $data;
    }
}
