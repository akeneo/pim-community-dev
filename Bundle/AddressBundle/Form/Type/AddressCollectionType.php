<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Oro\Bundle\AddressBundle\Entity\AddressBase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'type'           => 'oro_address_typed',
                'allow_add'      => true,
                'allow_delete'   => true,
                'by_reference'   => false,
                'prototype'      => true,
                'prototype_name' => '__name__',
                'label'          => ' ',
                'validation_groups' => function (FormInterface $form) {
                    /** @var AddressBase[] $data */
                    $data = $form->getData();
                    $hasAddress = false;
                    foreach ($data as $item) {
                        if (!$item->isEmpty()) {
                            $hasAddress = true;
                            break;
                        }
                    }
                    if ($hasAddress) {
                        return array('Default');
                    } else {
                        return array();
                    }
                },
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address_collection';
    }
}
