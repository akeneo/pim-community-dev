<?php

namespace Oro\Bundle\FormBundle\JsValidation\EventListener;

use Oro\Bundle\FormBundle\JsValidation\Event\PostProcessEvent;

class RepeatedTypeListener
{
    public function onPostProcess(PostProcessEvent $event)
    {
        $view = $event->getFormView();

        if (!isset($view->vars['type']) || $view->vars['type'] !== 'repeated') {
            return;
        }

        $repeatedNames = array_keys($view->vars['value']);
        $first = $view->children[$repeatedNames[0]];
        $second = $view->children[$repeatedNames[1]];

        if (isset($view->vars['attr']['data-validation'])) {
            if (!isset($first->vars['attr'])) {
                $first->vars['attr'] = array();
            }
            $first->vars['attr']['data-validation'] = $view->vars['attr']['data-validation'];
        }
        unset($view->vars['attr']['data-validation']);

        $secondValue = array();
        $secondValue['Repeated'] = array(
            'first_name' => $repeatedNames[0],
            'second_name' => $repeatedNames[1],
            'invalid_message' => $view->vars['invalid_message'],
            'invalid_message_parameters' => $view->vars['invalid_message_parameters'],
        );

        if (!isset($second->vars['attr'])) {
            $second->vars['attr'] = array();
        }
        $second->vars['attr']['data-validation'] = $secondValue;
    }
}
