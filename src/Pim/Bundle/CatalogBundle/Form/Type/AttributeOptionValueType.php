<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Type for option value attribute form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOptionValueType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFieldId($builder);

        $this->addFieldLocale($builder);

        $this->addFieldValue($builder);
    }

    /**
     * Add field id to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldId(FormBuilderInterface $builder)
    {
        $builder->add('id', 'hidden');
    }

    /**
     * Add field locale to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldLocale(FormBuilderInterface $builder)
    {
        $builder->add('locale', 'hidden');
    }

    /**
     * Add field value to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldValue(FormBuilderInterface $builder)
    {
        $builder->add('value', null, array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_attribute_option_value';
    }
}
