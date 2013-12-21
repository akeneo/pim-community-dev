<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber;
use Pim\Bundle\CatalogBundle\Manager\AttributeTypeManager;

/**
 * Type for attribute form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeType extends AbstractType
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
     * @var string
     */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param string                                  $attributeClass Attribute class
     * @param AttributeTypeManager                    $attTypeManager Attribute type manager
     * @param AddAttributeTypeRelatedFieldsSubscriber $subscriber     Subscriber to add attribute type related fields
     */
    public function __construct(
        $attributeClass,
        AttributeTypeManager $attTypeManager = null,
        AddAttributeTypeRelatedFieldsSubscriber $subscriber = null
    ) {
        $this->attributeClass = $attributeClass;
        $this->attTypeManager = $attTypeManager;
        $this->subscriber     = $subscriber;
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

        $this->addFieldUnique($builder);

        $this->addFieldLabel($builder);

        $this->addFieldUseableAsGridColumn($builder);

        $this->addFieldUseableAsGridFilter($builder);

        $this->addFieldAttributeGroup($builder);

        $this->addSubscriber($builder);
    }

    /**
     * Add subscriber
     * @param FormBuilderInterface $builder
     */
    protected function addSubscriber(FormBuilderInterface $builder)
    {
        $factory = $builder->getFormFactory();
        $this->subscriber->setFactory($factory);
        $builder->addEventSubscriber($this->subscriber);
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
     * Add field code to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldCode(FormBuilderInterface $builder)
    {
        $builder->add('code', 'text', array('required' => true));
    }

    /**
     * Add field unique to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUnique(FormBuilderInterface $builder)
    {
        $builder->add('unique', 'choice', array('choices' => array('No', 'Yes')));
    }

    /**
     * {@inheritdoc}
     */
    protected function addFieldAttributeType(FormBuilderInterface $builder)
    {
        $builder->add(
            'attributeType',
            'choice',
            array(
                'choices' => $this->getAttributeTypeChoices(),
                'select2' => true
            )
        );
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
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\ProductAttributeTranslation',
                'entity_class'      => $this->attributeClass,
                'property_path'     => 'translations'
            )
        );
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
                'class'       => 'Pim\Bundle\CatalogBundle\Entity\AttributeGroup',
                'required'    => false,
                'multiple'    => false,
                'empty_value' => AttributeGroup::DEFAULT_GROUP_CODE,
                'select2'     => true
            )
        );
    }

    /**
     * Add a field for useableAsGridColumn
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUseableAsGridColumn(FormBuilderInterface $builder)
    {
        $builder->add('useableAsGridColumn', 'switch');
    }

    /**
     * Add a field for useableAsGridFilter
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUseableAsGridFilter(FormBuilderInterface $builder)
    {
        $builder->add('useableAsGridFilter', 'switch');
    }

    /**
     * Add field required to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldRequired(FormBuilderInterface $builder)
    {
        $builder->add('required', 'switch');
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
        $resolver->setDefaults(array('data_class' => $this->attributeClass));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_attribute';
    }
}
