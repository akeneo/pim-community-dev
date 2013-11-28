<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Abstract option attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractOptionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $attribute = $value->getAttribute();
        $options['empty_value']   = false;
        $options['class']         = 'PimFlexibleEntityBundle:AttributeOption';
        $options['query_builder'] = function (EntityRepository $repository) use ($attribute) {
            return $repository->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };

        return $options;
    }
}
