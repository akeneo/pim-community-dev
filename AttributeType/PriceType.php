<?php
namespace Pim\Bundle\ProductBundle\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

/**
 * Price attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class PriceType extends AbstractAttributeType
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * Constructor
     *
     * @param string        $backendType the backend type
     * @param string        $formType    the form type
     * @param LocaleManager $manager     the locale manager
     */
    public function __construct($backendType, $formType, LocaleManager $manager)
    {
        parent::__construct($backendType, $formType);

        $this->localeManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['empty_value']   = false;
        $options['class']         = 'PimProductBundle:ProductValuePrice';
        $options['query_builder'] = function (EntityRepository $er) use ($value) {
            return $er->createQueryBuilder('price')->where('price.value = '.$value->getId());
        };

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_price';
    }
}
