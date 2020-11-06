<?php

namespace Akeneo\Pim\Enrichment\Bundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for category form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryType extends AbstractType
{
    /** @var string Entity FQCN */
    protected $dataClass;

    /** @var string Translation entity FQCN */
    protected $translationDataClass;

    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

    /**
     * Constructor
     *
     * @param string $dataClass
     * @param string $translationDataClass
     */
    public function __construct(string $dataClass, string $translationDataClass)
    {
        $this->dataClass = $dataClass;
        $this->translationDataClass = $translationDataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
    protected function addLabelField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'label',
            TranslatableFieldType::class,
            [
                'field'             => 'label',
                'translation_class' => $this->translationDataClass,
                'entity_class'      => $this->dataClass,
                'property_path'     => 'translations'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'  => $this->dataClass
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'pim_category';
    }

    /**
     * Add an event subscriber
     *
     * @param EventSubscriberInterface $subscriber
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->subscribers[] = $subscriber;
    }
}
