<?php
namespace Pim\Bundle\ProductBundle\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Multi options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @abstract
 */
abstract class AbstractEntitySelectType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options   = parent::prepareValueFormOptions($value);
        $attribute = $value->getAttribute();
        $orderBy   = $this->getEntityFieldToOrder();
        $options['empty_value'] = false;
        $options['class']       = $this->getEntityAlias();
        $options['expanded']    = false;
        $options['multiple']    = $this->isMultiselect();
        $options['query_builder'] = function (EntityRepository $er) use ($orderBy) {
            return $er->createQueryBuilder('o')->orderBy('o.'.$orderBy);
        };

        return $options;
    }

    /**
     * Is multiselect
     *
     * @return boolean
     *
     * @abstract
     */
    protected abstract function isMultiselect();

    /**
     * Get entity field to order
     *
     * @return string
     *
     * @abstract
     */
    protected abstract function getEntityFieldToOrder();

    /**
     * Get joined entity alias
     *
     * @return string
     *
     * @abstract
     */
    protected abstract function getEntityAlias();
}
