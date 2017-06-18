<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for group form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupType extends AbstractType
{
    /** @var string */
    protected $attributeClass;

    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

    /** @var string */
    protected $dataClass;

    /**
     * Constructor
     *
     * @param string $attributeClass
     * @param string $dataClass
     */
    public function __construct($attributeClass, $dataClass)
    {
        $this->attributeClass = $attributeClass;
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->addEventSubscriber(new DisableFieldSubscriber('code'));

        $this->addTypeField($builder);

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_enrich_group';
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

    /**
     * Add type field
     *
     * @param FormBuilderInterface $builder
     */
    protected function addTypeField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'type',
                EntityType::class,
                [
                    'class'         => 'PimCatalogBundle:GroupType',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->getAllGroupsExceptVariantQB();
                    },
                    'multiple' => false,
                    'expanded' => false,
                    'select2'  => true
                ]
            )
            ->addEventSubscriber(new DisableFieldSubscriber('type', 'getType'));
    }
}
