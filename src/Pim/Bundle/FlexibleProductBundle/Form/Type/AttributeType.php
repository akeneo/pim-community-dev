<?php
namespace Pim\Bundle\FlexibleProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Model\Attribute\Type\AbstractAttributeType;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Type for attribute form (independant of persistence)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeType extends AbstractType
{

    const FRONTEND_TYPE_TEXTFIELD = 'Text Field';
    const FRONTEND_TYPE_TEXTAREA  = 'Text Area';
    const FRONTEND_TYPE_PRICE     = 'Price';
    const FRONTEND_TYPE_DATE      = 'Date';
    const FRONTEND_TYPE_LIST      = 'List';
    const FRONTEND_TYPE_IMAGE     = 'Image';
    const FRONTEND_TYPE_FILE      = 'File';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFieldId($builder);

        $this->addFieldCode($builder);

        $this->addFieldFrontendType($builder);

        $builder->add('backend_storage', 'hidden', array(
            'data' => AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE
        ));

        $this->addFieldRequired($builder);

        $this->addFieldUnique($builder);

        $this->addFieldDefaultValue($builder);

        $this->addFieldSearchable($builder);

        $this->addFieldTranslatable($builder);

        $this->addFieldScopable($builder);

        // only on edit
        $this->addFieldOptions($builder);
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
     * Add field frontend type to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldFrontendType(FormBuilderInterface $builder)
    {
        $builder->add(
            'backend_type',
            'choice',
            array(
                'choices'  => array(
                    AbstractAttributeType::BACKEND_TYPE_VARCHAR => self::FRONTEND_TYPE_TEXTFIELD,
                    AbstractAttributeType::BACKEND_TYPE_TEXT    => self::FRONTEND_TYPE_TEXTAREA,
                    AbstractAttributeType::BACKEND_TYPE_DECIMAL => self::FRONTEND_TYPE_PRICE,
                    AbstractAttributeType::BACKEND_TYPE_DATE    => self::FRONTEND_TYPE_DATE,
                    AbstractAttributeType::BACKEND_TYPE_OPTION  => self::FRONTEND_TYPE_LIST,
//                     AbstractAttributeType::BACKEND_TYPE_VARCHAR => self::FRONTEND_TYPE_IMAGE,
//                     AbstractAttributeType::BACKEND_TYPE_VARCHAR => self::FRONTEND_TYPE_FILE
                )
            )
        );
    }

    /**
     * Add field required to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldRequired(FormBuilderInterface $builder)
    {
        $builder->add('required', 'checkbox', array('required' => false));
    }

    /**
     * Add field unique to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUnique(FormBuilderInterface $builder)
    {
        $builder->add('unique', 'checkbox', array('required' => false));
    }

    /**
     * Add field default value to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldDefaultValue(FormBuilderInterface $builder)
    {
        $builder->add('default_value', 'text', array('required' => false));
    }

    /**
     * Add field searchable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldSearchable(FormBuilderInterface $builder)
    {
        $builder->add('searchable', 'checkbox', array('required' => false));
    }

    /**
     * Add field translatable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldTranslatable(FormBuilderInterface $builder)
    {
        $builder->add('translatable', 'checkbox', array('required' => false));
    }

    /**
     * Add field scopable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldScopable(FormBuilderInterface $builder)
    {
        $builder->add('scopable', 'choice', array(
            'choices' => array(
                'Global',
                'Channel'
            )
        ));
    }

    /**
     * Add option fields to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldOptions(FormBuilderInterface $builder)
    {
        $builder->add(
            'options', 'collection', array(
                'type'         => new AttributeOptionType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute'
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
