<?php
namespace Pim\Bundle\FlexibleProductBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Type for product attribute form (independant of persistence)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductAttributeType extends AbstractType
{

    /**
     * Product attribute class full name
     * @var string
     */
    protected $productAttClass;

    /**
     * Construct with full name of concrete impl of attribute and option classes
     * @param string $productAttClass Product attribute full classname
     */
    public function __construct($productAttClass)
    {
        $this->productAttClass = $productAttClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        $builder->add('name', 'text');

        $builder->add('description', 'textarea');

        $builder->add('smart', 'checkbox', array('required' => false));

        $builder->add('attribute', new AttributeType());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->productAttClass
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleproduct_productattributetype';
    }
}
