<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\SegmentationTreeBundle\Form\Type\AbstractSegmentType;

/**
 * Type for category form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryType extends AbstractSegmentType
{
    /**
     * Entity FQCN
     *
     * @var string
     */
    protected $className;

    /**
     * Translation entity FQCN
     *
     * @var string
     */
    protected $translationClassName;

    /**
     * Constructor
     *
     * @param string $className
     * @param string $translationClassName
     */
    public function __construct($className, $translationClassName)
    {
        $this->className = $className;
        $this->translationClassName = $translationClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->addTitleField($builder);

        /*
        // Add isDynamic field is needed
        $subscriber = new CategorySubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
        */
    }

    /**
     * Add title field
     *
     * @param FormBuilderInterface $builder
     */
    protected function addTitleField(FormBuilderInterface $builder)
    {
        $builder->add(
            'title',
            'pim_translatable_field',
            array(
                'field'             => 'title',
                'translation_class' => 'Pim\\Bundle\\ProductBundle\\Entity\\CategoryTranslation',
                'entity_class'      => 'Pim\\Bundle\\ProductBundle\\Entity\\Category',
                'property_path'     => 'translations'
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
                'data_class' => $this->className
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_category';
    }
}
