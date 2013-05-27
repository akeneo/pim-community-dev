<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Abstract option attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
        $options['class']         = 'OroFlexibleEntityBundle:AttributeOption';
        $options['query_builder'] = function (EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };

        return $options;
    }
}
