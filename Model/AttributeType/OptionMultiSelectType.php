<?php
namespace Pim\Bundle\ProductBundle\Model\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Multi options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class OptionMultiSelectType extends AbstractAttributeType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->backendType = self::BACKEND_TYPE_OPTIONS;
        $this->formType    = 'entity';
    }

    /**
     * Get form type options
     *
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    public function prepareFormOptions(AbstractAttribute $attribute)
    {
        $options = parent::prepareFormOptions($attribute);
        $options['empty_value']   = false;
        $options['class']         = 'PimProductBundle:AttributeOption';
        $options['query_builder'] = function (EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };
        $options['expanded'] = false;
        $options['multiple'] = true;

        return $options;
    }
}
