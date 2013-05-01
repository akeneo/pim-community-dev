<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Pim\Bundle\ProductBundle\Form\Subscriber\AddValueFieldSubscriber;

use Pim\Bundle\ProductBundle\Manager\ProductManager;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;
use Pim\Bundle\ProductBundle\Form\Subscriber\AddAttributeGroupSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Product value form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueType extends FlexibleValueType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_value';
    }
}
