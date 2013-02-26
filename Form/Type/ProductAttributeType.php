<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\FormInterface;

use Pim\Bundle\ProductBundle\Form\Subscriber\ProductAttributeSubscriber;

use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormEvents;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Type for attribute form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeType extends AttributeType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->addFieldName($builder);

        $this->addFieldDescription($builder);

        $this->addFieldVariantBehavior($builder);

        $this->addFieldSmart($builder);

        $this->addAttributeGroup($builder);

        // Add a subscriber
        $factory = $builder->getFormFactory();
        $subscriber = new ProductAttributeSubscriber($factory);
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    protected function addFieldScopable(FormBuilderInterface $builder)
    {
        // use custom scope notion pofor product
        $builder->add(
            'scopable',
            'choice',
            array(
                'choices' => array(
                    0 => 'Global',
                    1 =>'Channel'
                )
            )
        );
    }

    /**
     * Add a field for name
     * @param FormBuilderInterface $builder
     */
    protected function addFieldName(FormBuilderInterface $builder)
    {
        $builder->add('name');
    }

    /**
     * Add a field for description
     * @param FormBuilderInterface $builder
     */
    protected function addFieldDescription(FormBuilderInterface $builder)
    {
        $builder->add('description', 'textarea');
    }

    /**
     * Add a field for variant behavior
     * @param FormBuilderInterface $builder
     */
    protected function addFieldVariantBehavior(FormBuilderInterface $builder)
    {
        $builder->add(
            'variant',
            'choice',
            array(
                'choices' => array(
                    0 => 'Always override',
                    1 => 'A selection of variants',
                    2 => 'Ask'
                )
            )
        );
    }

    /**
     * Add a field for smart
     * @param FormBuilderInterface $builder
     */
    protected function addFieldSmart(FormBuilderInterface $builder)
    {
        $builder->add('smart', 'checkbox', array('required' => false));
    }

    /**
     * Add a field for attribute group
     * @param FormBuilderInterface $builder
     */
    protected function addAttributeGroup(FormBuilderInterface $builder)
    {
        $builder->add('group');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\ProductAttribute'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_attribute';
    }
}
