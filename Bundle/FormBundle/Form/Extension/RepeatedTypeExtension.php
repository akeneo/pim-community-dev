<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class RepeatedTypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'repeated';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['type'] = 'repeated';
        $view->vars['invalid_message'] = $options['invalid_message'];
        $view->vars['invalid_message_parameters'] = $options['invalid_message_parameters'];
    }
}
