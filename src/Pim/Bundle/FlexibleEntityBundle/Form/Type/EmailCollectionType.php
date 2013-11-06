<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Email collection type
 */
class EmailCollectionType extends CollectionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'collection',
            'collection',
            array(
                'type'           => new EmailType(),

                'allow_add'      => true,
                'allow_delete'   => true,
                'by_reference'   => false,
                'prototype'      => true,
                'prototype_name' => '__name__',
                'label'          => ' '
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_email_collection';
    }
}
