<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeType;

use Pim\Bundle\ProductBundle\Form\Subscriber\ProductAttributeSubscriber;
use Pim\Bundle\ProductBundle\Service\AttributeService;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use Doctrine\ORM\EntityRepository;

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
     * Attribute service
     * @var AttributeService
     */
    private $attributeService;

    /**
     * Constructor
     *
     * @param AttributeService $attributeService
     */
    public function __construct(AttributeService $attributeService = null)
    {
        $this->attributeService = $attributeService;
    }

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

        $this->addFieldUseableAsGridColumn($builder);

        $this->addFieldUseableAsGridFilter($builder);

        $this->addFieldAttributeGroup($builder);

        $this->addFieldAvailableLanguages($builder);
    }

    /**
     * Add subscriber
     * @param FormBuilderInterface $builder
     */
    protected function addSubscriber(FormBuilderInterface $builder)
    {
        // add our own subscriber for custom features
        $factory = $builder->getFormFactory();
        $subscriber = new ProductAttributeSubscriber($factory, $this->attributeService);
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * Add a field for name
     * @param FormBuilderInterface $builder
     */
    protected function addFieldName(FormBuilderInterface $builder)
    {
        $builder->add(
            'name',
            'pim_translatable_field',
            array(
                'field'             => 'name',
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
        $builder->add('smart', 'checkbox');
    }

    /**
     * Add a field for attribute group
     * @param FormBuilderInterface $builder
     */
    protected function addFieldAttributeGroup(FormBuilderInterface $builder)
    {
        $builder->add('group');
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
     * Add field searchable to form builder
     * @param FormBuilderInterface $builder
     */
    protected function addFieldSearchable(FormBuilderInterface $builder)
    {
        $builder->add('searchable', 'checkbox');
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
     * Override the parent's addFieldUnique method to prevent adding
     * unique field regardless of attribute type
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldUnique(FormBuilderInterface $builder)
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
     * Override the parent's addFieldScopable method to prevent adding
     * scopable field regardless of attribute type
     *
     * @param FormBuilderInterface $builder
     */
    protected function addFieldScopable(FormBuilderInterface $builder)
    {
    }

    /**
     * Add a field for available languages
     * @param FormBuilderInterface $builder
     *
     * @return void
     */
    protected function addFieldAvailableLanguages(FormBuilderInterface $builder)
    {
        $builder->add(
            'availableLanguages',
            'entity',
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Pim\Bundle\ConfigBundle\Entity\Language',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('l')->where('l.activated = 1')->orderBy('l.code');
                }
            )
        );
    }

    /**
     * Return available frontend type
     *
     * @return array
     */
    public function getAttributeTypeChoices()
    {
        return $this->attributeService->getAttributeTypes();
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
