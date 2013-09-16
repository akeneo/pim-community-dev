<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use APY\JsFormValidationBundle\EventListener\RepeatedFieldListener;
use APY\JsFormValidationBundle\Generator\PostProcessEvent;

class EmbedRepeatedFieldListener extends RepeatedFieldListener
{
    public function onJsfvPostProcess(PostProcessEvent $event)
    {
        $formFields = $event->getFormView()->children;
        $fieldsConstraints = $event->getFieldsConstraints();

        foreach ($formFields as $formField) {
            foreach ($formField->children as $childField) {
                if (isset($childField->vars['type']) && $childField->vars['type'] == 'repeated') {
                    $event = new PostProcessEvent($formField, $fieldsConstraints);
                    parent::onJsfvPostProcess($event);
                }
            }
        }
    }
}
