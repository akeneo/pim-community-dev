<?php

namespace Pim\Bundle\FilterBundle\Filter\Flexible;

use Oro\Bundle\FilterBundle\Filter\NumberFilter as OroNumberFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

use Pim\Bundle\FilterBundle\Form\Type\Filter\PriceFilterType;

/**
 * Price filter related to flexible entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends OroNumberFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return PriceFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $this->util->applyFlexibleFilter(
            $ds,
            $this->get(FilterUtility::FEN_KEY),
            $this->get(FilterUtility::DATA_NAME_KEY),
            sprintf('%s %s', $data['value'], $data['currency']),
            $this->getOperator($data['type'])
        );

        return true;
    }

    /**
     * Overriden to validate currency option
     *
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $data = parent::parseData($data);

        if (!is_array($data) || !array_key_exists('currency', $data) || !is_string($data['currency'])) {
            return false;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $formView = $this->getForm()->createView();
        $metadata['currencies'] = $formView->vars['currency_choices'];

        return $metadata;
    }
}
