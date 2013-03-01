<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for product family form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductFamilyType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->addFieldName($builder);

        $this->addFieldDescription($builder);

        $this->addFieldAttributes($builder);
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
     * Add a field for attributes
     * @param FormBuilderInterface $builder
     */
    protected function addFieldAttributes(FormBuilderInterface $builder)
    {
        $builder->add('attributes');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\ProductFamily'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_family';
    }
}
