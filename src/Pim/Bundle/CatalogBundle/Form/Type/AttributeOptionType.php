<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Type for option attribute form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeOptionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFieldId($builder);

        $this->addFieldSortOrder($builder);

        $this->addFieldTranslatable($builder);

        $this->addFieldOptionValues($builder);

        $this->addFieldCode($builder);

        $this->addFieldIsDefault($builder);
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
     * Add field sort_order to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldSortOrder(FormBuilderInterface $builder)
    {
        $builder->add('sort_order', 'integer', ['required' => false]);
    }

    /**
     * Add field translatable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldTranslatable(FormBuilderInterface $builder)
    {
        $builder->add('translatable', null, ['required' => false]);
    }

    /**
     * Add option code
     * @param FormBuilderInterface $builder
     */
    protected function addFieldCode(FormBuilderInterface $builder)
    {
        $builder->add('code', 'text', ['required' => true]);
    }

    /**
     * Add options values to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldOptionValues(FormBuilderInterface $builder)
    {
        $builder->add(
            'optionValues',
            'collection',
            [
                'type'         => 'pim_catalog_attribute_option_value',
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            ]
        );
    }

    /**
     * Add isDefault field to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldIsDefault(FormBuilderInterface $builder)
    {
        $builder->add('default', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\AttributeOption'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_attribute_option';
    }
}
