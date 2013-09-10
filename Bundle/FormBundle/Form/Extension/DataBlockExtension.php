<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DataBlockExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(
            array(
                'block',
                'subblock',
                'block_config',
                'tooltip'
            )
        );
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['block'])) {
            $view->vars['block'] = $options['block'];
        }

        if (isset($options['subblock'])) {
            $view->vars['subblock'] = $options['subblock'];
        }

        if (isset($options['block_config'])) {
            $view->vars['block_config'] = $options['block_config'];
        }

        if (isset($options['tooltip'])) {
            $view->vars['tooltip'] = $options['tooltip'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
