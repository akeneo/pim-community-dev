<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Component\Catalog\AttributeTypeRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for attribute form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeType extends AbstractType
{
    /** @var AttributeTypeRegistry */
    protected $registry;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $attributeTranslation;

    /** @var string */
    protected $attributeGroupClass;

    /**
     * Constructor
     *
     * @param AttributeTypeRegistry                   $registry
     * @param string                                  $attributeTranslation
     * @param string                                  $attributeClass       Attribute class
     * @param string                                  $attributeGroupClass
     */
    public function __construct(
        AttributeTypeRegistry $registry,
        $attributeTranslation,
        $attributeClass,
        $attributeGroupClass
    ) {
        $this->registry             = $registry;
        $this->attributeClass       = $attributeClass;
        $this->attributeTranslation = $attributeTranslation;
        $this->attributeGroupClass  = $attributeGroupClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFieldId($builder);

        $this->addFieldCode($builder);

        $this->addFieldAttributeType($builder);

        $this->addFieldRequired($builder);

        $this->addFieldLabel($builder);

        $this->addFieldUseableAsGridFilter($builder);

        $this->addFieldAttributeGroup($builder);
    }

    /**
     * Add field id to form builder
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldId(FormBuilderInterface $builder)
    {
        $builder->add('id', 'hidden');
    }

    /**
     * Add field code to form builder
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldCode(FormBuilderInterface $builder)
    {
        $builder->add('code', 'text', ['required' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addFieldAttributeType(FormBuilderInterface $builder)
    {
        $builder->add(
            'attributeType',
            'choice',
            [
                'choices'   => $this->registry->getSortedAliases(),
                'select2'   => true,
                'disabled'  => false,
                'read_only' => true
            ]
        );
    }

    /**
     * Add a field for label
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldLabel(FormBuilderInterface $builder)
    {
        $builder->add(
            'label',
            'pim_translatable_field',
            [
                'field'             => 'label',
                'translation_class' => $this->attributeTranslation,
                'entity_class'      => $this->attributeClass,
                'property_path'     => 'translations'
            ]
        );
    }

    /**
     * Add a field for attribute group
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldAttributeGroup(FormBuilderInterface $builder)
    {
        $builder->add(
            'group',
            'entity',
            [
                'class'       => $this->attributeGroupClass,
                'required'    => true,
                'multiple'    => false,
                'empty_value' => 'Choose the attribute group',
                'select2'     => true
            ]
        );
    }

    /**
     * Add a field for useableAsGridFilter
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUseableAsGridFilter(FormBuilderInterface $builder)
    {
        $builder->add('useableAsGridFilter', 'switch');
    }

    /**
     * Add field required to form builder
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldRequired(FormBuilderInterface $builder)
    {
        $builder->add('required', 'switch');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => $this->attributeClass,
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_attribute';
    }
}
