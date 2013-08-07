<?php

namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeType;
use Pim\Bundle\ProductBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber;
use Pim\Bundle\ProductBundle\Manager\AttributeTypeManager;

/**
 * Type for attribute form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeType extends AttributeType
{
    /**
     * Attribute type manager
     * @var AttributeTypeManager
     */
    protected $attTypeManager;

    /**
     * Attribute subscriber
     * @var AddAttributeTypeRelatedFieldsSubscriber
     */
    protected $subscriber;

    /**
     * Constructor
     *
     * @param AttributeTypeManager                    $attTypeManager Attribute type manager
     * @param AddAttributeTypeRelatedFieldsSubscriber $subscriber     Subscriber to add attribute type related fields
     */
    public function __construct(
        AttributeTypeManager $attTypeManager = null,
        AddAttributeTypeRelatedFieldsSubscriber $subscriber = null
    ) {
        $this->attTypeManager = $attTypeManager;
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->addFieldLabel($builder);

        $this->addFieldDescription($builder);

        $this->addFieldVariantBehavior($builder);

        $this->addFieldSmart($builder);

        $this->addFieldUseableAsGridColumn($builder);

        $this->addFieldUseableAsGridFilter($builder);

        $this->addFieldAttributeGroup($builder);
    }

    /**
     * Add subscriber
     * @param FormBuilderInterface $builder
     */
    protected function addSubscriber(FormBuilderInterface $builder)
    {
        // add our own subscriber for custom features
        $factory = $builder->getFormFactory();
        $this->subscriber->setFactory($factory);
        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * Add a field for label
     * @param FormBuilderInterface $builder
     */
    protected function addFieldLabel(FormBuilderInterface $builder)
    {
        $builder->add(
            'label',
            'pim_translatable_field',
            array(
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\ProductBundle\\Entity\\ProductAttributeTranslation',
                'entity_class'      => 'Pim\\Bundle\\ProductBundle\\Entity\\ProductAttribute',
                'property_path'     => 'translations'
            )
        );
    }

    /**
     * Add a field for description
     * @param FormBuilderInterface $builder
     */
    protected function addFieldDescription(FormBuilderInterface $builder)
    {
        $builder->add('description', 'textarea', array('required' => false));
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
        $builder->add('smart', 'checkbox');
    }

    /**
     * Add a field for attribute group
     * @param FormBuilderInterface $builder
     */
    protected function addFieldAttributeGroup(FormBuilderInterface $builder)
    {
        $builder->add(
            'group',
            'entity',
            array(
                'class' => 'Pim\Bundle\ProductBundle\Entity\AttributeGroup',
                'required' => false,
                'multiple' => false,
                'empty_value' => 'Other'
            )
        );
    }

    /**
     * Add a field for useableAsGridColumn
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUseableAsGridColumn(FormBuilderInterface $builder)
    {
        $builder->add('useableAsGridColumn', 'checkbox');
    }

    /**
     * Add a field for useableAsGridFilter
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUseableAsGridFilter(FormBuilderInterface $builder)
    {
        $builder->add('useableAsGridFilter', 'checkbox');
    }

    /**
     * Add field required to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldRequired(FormBuilderInterface $builder)
    {
        $builder->add('required', 'checkbox');
    }

    /**
     * Override the parent's addFieldSearchable method to prevent adding
     * searchable field regardless of attribute type
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldSearchable(FormBuilderInterface $builder)
    {
    }

     /**
     * Override the parent's addFieldDefaultValue method to prevent adding
     * default value field regardless of attribute type
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldDefaultValue(FormBuilderInterface $builder)
    {
    }

    /**
     * Override the parent's addFieldTranslatable method to prevent adding
     * translatable field regardless of attribute type
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldTranslatable(FormBuilderInterface $builder)
    {
    }

    /**
     * Override the parent's addFieldUnique method to prevent adding
     * unique field regardless of attribute type
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUnique(FormBuilderInterface $builder)
    {
    }

    /**
     * Override the parent's addFieldScopable method to prevent adding
     * scopable field regardless of attribute type
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldScopable(FormBuilderInterface $builder)
    {
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return void
     */
    protected function addPositionField(FormBuilderInterface $builder)
    {
    }


    /**
     * Return available frontend type
     *
     * @return array
     */
    public function getAttributeTypeChoices()
    {
        return $this->attTypeManager->getAttributeTypes();
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
