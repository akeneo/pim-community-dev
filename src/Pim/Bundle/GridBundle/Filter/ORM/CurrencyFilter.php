<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CurrencyFilterType;

/**
 * Currency filter for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyFilter extends NumberFilter
{

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => CurrencyFilterType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        list($formType, $formOptions) = parent::getRenderSettings();

        $dataType = $this->getOption('data_type', FieldDescriptionInterface::TYPE_DECIMAL);
        switch ($dataType) {
            case FieldDescriptionInterface::TYPE_DECIMAL:
                $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;
                break;
            case FieldDescriptionInterface::TYPE_INTEGER:
            default:
                $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;
        }

        return array($formType, $formOptions);
    }
}
