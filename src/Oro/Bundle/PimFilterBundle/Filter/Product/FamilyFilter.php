<?php

namespace Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\AjaxChoiceFilter;

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
        if (in_array(strtoupper($data['type']), [Operators::IS_EMPTY, Operators::IS_NOT_EMPTY])) {
            $this->util->applyFilter($dataSource, 'family', strtoupper($data['type']), null);
        } else {
            $this->util->applyFilter($dataSource, 'family', Operators::IN_LIST, $data['value']);
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
        $metadata[FilterUtility::TYPE_KEY] = 'select2-rest-choice';

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormOptions()
    {
        return array_merge(
            parent::getFormOptions(),
            [
                'choice_url' => 'pim_enrich_family_rest_index',
                'choice_url_params' => ['options' => ['expanded' => 0]]
            ]
        );
    }
}
