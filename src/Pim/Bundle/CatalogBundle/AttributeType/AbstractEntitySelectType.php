<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Multi options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $options        = parent::prepareValueFormOptions($value);
        $orderBy        = $this->getEntityFieldToOrder();
        $isTranslatable = $this->isTranslatable();
        $options['empty_value'] = '';
        $options['class']       = $this->getEntityAlias();
        $options['expanded']    = false;
        $options['multiple']    = $this->isMultiselect();
        $options['query_builder'] = function (EntityRepository $repository) use ($orderBy, $isTranslatable) {
            $qb = $repository->createQueryBuilder('o');
            if ($isTranslatable) {
                $qb->addSelect('translation')->leftJoin('o.translations', 'translation');
            }

            return $qb->orderBy('o.'.$orderBy);
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
    abstract protected function isMultiselect();

    /**
     * Is translatable
     *
     * @return boolean
     *
     * @abstract
     */
    abstract protected function isTranslatable();

    /**
     * Get entity field to order
     *
     * @return string
     *
     * @abstract
     */
    abstract protected function getEntityFieldToOrder();

    /**
     * Get joined entity alias
     *
     * @return string
     *
     * @abstract
     */
    abstract protected function getEntityAlias();
}
