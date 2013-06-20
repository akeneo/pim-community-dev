<?php
namespace Pim\Bundle\ProductBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Multi options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractEntitySelectType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $attribute = $value->getAttribute();
        $options['empty_value'] = false;
        $options['class']       = $this->getEntityAlias();
        $options['expanded']    = false;
        $options['multiple']    = $this->isMultiselect();

        return $options;
    }

    /**
     * Is multiselect
     *
     * @return boolean
     */
    protected abstract function isMultiselect();

    /**
     * Get joined entity alias
     *
     * @return string
     */
    protected abstract function getEntityAlias();
}
