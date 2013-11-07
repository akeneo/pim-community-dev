<?php

namespace Oro\Bundle\LocaleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CurrencyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => Intl::getCurrencyBundle()->getCurrencyNames('en'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'currency';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_currency';
    }
}
