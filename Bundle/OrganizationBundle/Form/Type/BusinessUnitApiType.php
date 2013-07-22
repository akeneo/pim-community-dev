<?php

namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;

class BusinessUnitApiType extends BusinessUnitType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            array(
                'data_class'      => 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit',
                'intention'       => 'business_unit',
                'csrf_protection' => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'business_unit';
    }
}
