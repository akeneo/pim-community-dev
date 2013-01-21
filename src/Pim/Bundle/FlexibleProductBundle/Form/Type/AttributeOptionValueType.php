<?php
namespace Pim\Bundle\FlexibleProductBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Type for option value attribute form (independent of persistence)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeOptionValueType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFieldId($builder);

        $this->addFieldLocaleCode($builder);

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
     * Add field locale_code to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldLocaleCode(FormBuilderInterface $builder)
    {
        $builder->add('locale_code');
    }

    /**
     * Add field value to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldValue(FormBuilderInterface $builder)
    {
        $builder->add('value');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleproduct_productattribute';
    }
}