<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

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

    /** @var AddAttributeTypeRelatedFieldsSubscriber Attribute subscriber */
    protected $subscriber;

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
     * @param AddAttributeTypeRelatedFieldsSubscriber $subscriber           Subscriber to add attribute type
     *                                                                      related fields
     * @param string                                  $attributeTranslation
     * @param string                                  $attributeClass       Attribute class
     * @param string                                  $attributeGroupClass
     */
    public function __construct(
        AttributeTypeRegistry $registry,
        AddAttributeTypeRelatedFieldsSubscriber $subscriber,
        $attributeTranslation,
        $attributeClass,
        $attributeGroupClass
    ) {
        $this->registry = $registry;
        $this->subscriber = $subscriber;
        $this->attributeClass = $attributeClass;
        $this->attributeTranslation = $attributeTranslation;
        $this->attributeGroupClass = $attributeGroupClass;
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

        $this->addSubscriber($builder);
    }

    /**
     * Add subscriber
     *
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
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldId(FormBuilderInterface $builder)
    {
        $builder->add('id', HiddenType::class);
    }

    /**
     * Add field code to form builder
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldCode(FormBuilderInterface $builder)
    {
        $builder->add('code', TextType::class, ['required' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addFieldAttributeType(FormBuilderInterface $builder)
    {
        $builder->add(
            'type',
            ChoiceType::class,
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
            TranslatableFieldType::class,
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
            EntityType::class,
            [
                'class'         => $this->attributeGroupClass,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->orderBy('g.sortOrder', 'ASC');
                },
                'required'      => true,
                'multiple'      => false,
                'empty_value'   => 'Choose the attribute group',
                'select2'       => true
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
        $builder->add('useableAsGridFilter', SwitchType::class);
    }

    /**
     * Add field required to form builder
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldRequired(FormBuilderInterface $builder)
    {
        $builder->add('required', SwitchType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'  => $this->attributeClass,
                'constraints' => new Valid()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_enrich_attribute';
    }
}
