<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Collection item
 */
abstract class CollectionItemAbstract extends AbstractType
{
    /**
     * Returns choices array form type select box
     *
     * @return mixed
     */
    abstract public function getTypesArray();

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class'    => 'Pim\Bundle\FlexibleEntityBundle\Entity\Collection',
                    'required'      => false
                )
            );
    }
}
