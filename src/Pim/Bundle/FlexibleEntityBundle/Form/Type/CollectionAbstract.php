<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Pim\Bundle\FlexibleEntityBundle\Form\EventListener\CollectionTypeSubscriber;

/**
 * Collection
 */
abstract class CollectionAbstract extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CollectionTypeSubscriber());
    }
}
