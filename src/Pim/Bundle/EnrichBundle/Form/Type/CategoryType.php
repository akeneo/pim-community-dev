<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;

/**
 * Type for category form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryType extends AbstractType
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

    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

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

        $builder->add('code');

        $this->addLabelField($builder);

        $builder->addEventSubscriber(new DisableFieldSubscriber('code'));

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
    }

    /**
     * Add label field
     *
     * @param FormBuilderInterface $builder
     */
    protected function addLabelField(FormBuilderInterface $builder)
    {
        $builder->add(
            'label',
            'pim_translatable_field',
            array(
                'field'             => 'label',
                'translation_class' => $this->translationClassName,
                'entity_class'      => $this->className,
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
                'data_class'  => $this->className
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

    /**
     * Add an event subscriber
     *
     * @param EventSubscriberInterface $subscriber
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }
}
