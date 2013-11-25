<?php

namespace Oro\Bundle\FormBundle\Form\Extension\JsValidation;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class RepeatedTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $first = $view->children[$options['first_name']];
        $second = $view->children[$options['second_name']];

        if (isset($view->vars['attr']['data-validation'])) {
            $first->vars['attr']['data-validation'] = $view->vars['attr']['data-validation'];
        }
        unset($view->vars['attr']['data-validation']);

        $secondValue = array();
        $secondValue['Repeated'] = array(
            'first_name' => $options['first_name'],
            'second_name' => $options['second_name'],
            'invalid_message' => $options['invalid_message'],
            'invalid_message_parameters' => $options['invalid_message_parameters'],
        );

        if (!isset($second->vars['attr'])) {
            $second->vars['attr'] = array();
        }
        $second->vars['attr']['data-validation'] = json_encode($secondValue);
    }

    public function getExtendedType()
    {
        return 'repeated';
    }
}
