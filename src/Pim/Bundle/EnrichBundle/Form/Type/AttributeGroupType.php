<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;

/**
 * Type for AttributeGroup form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupType extends AbstractType
{
    /** @var EventSubscriberInterface[] */
    protected $subscribers = [];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add(
                'label',
                'pim_translatable_field',
                array(
                    'field'             => 'label',
                    'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroupTranslation',
                    'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup',
                    'property_path'     => 'translations'
                )
            )
            ->add('sort_order', 'hidden')
            ->addEventSubscriber(new DisableFieldSubscriber('code'));

        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\AttributeGroup'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_attribute_group';
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
