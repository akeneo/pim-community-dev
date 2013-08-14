<?php

namespace Oro\Bundle\OrganizationBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class FormTypeExtension extends AbstractTypeExtension
{
    /**
    * Returns the name of the type being extended.
    *
    * @return string The name of the type being extended
    */
    public function getExtendedType()
    {
        return 'form';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {

    }
}