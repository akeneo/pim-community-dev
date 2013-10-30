<?php

namespace Oro\Bundle\SoapBundle\Form\Extension;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FormBundle\Form\Type\OroDateType;

class DateFormExtension extends AbstractApiFormExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OroDateType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        if ($this->isApiRequest()) {
            $resolver->setDefaults(array('localized_format' => false));
        }
    }
}
