<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigFormExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['allowed_type'])) {
            $fieldType = isset($options['field_type']) ? $options['field_type'] : null;

            $view->vars['attr'] = array_merge($view->vars['attr'], array('data-allowedType' => $options['allowed_type']));

            $types = explode(',', $options['allowed_type']);
            $types = array_map('trim', $types);
            if (!$fieldType || !in_array($fieldType, $types)) {
                $view->vars['attr']['class'] = (isset($view->vars['attr']['class']) ? $view->vars['attr']['class'] : '') . 'hide';
            }

            $view->vars['attr'] = array_merge($view->vars['attr'], array('data-fieldType' => $fieldType));
        }

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'allowed_type',
            'field_type'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
