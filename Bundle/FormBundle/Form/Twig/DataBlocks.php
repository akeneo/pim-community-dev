<?php

namespace Oro\Bundle\FormBundle\Form\Twig;

use Symfony\Component\Form\FormView;

use Oro\Bundle\FormBundle\Config\FormConfig;

class DataBlocks
{
    public static function render(\Twig_Environment $env, $context, FormView $form, $formVariableName = 'form')
    {
        if (isset($form->vars['formConfig']) && $form->vars['formConfig'] instanceof FormConfig) {
            $tmpLoader = $env->getLoader();
            $env->setLoader(new \Twig_Loader_String());

            /** @var FormConfig $formConfig */
            $formConfig = $form->vars['formConfig'];

            foreach ($form->children as $formChildName => $formChild) {
                foreach ($formChild->children as $childName => $child) {
                    if (isset($child->vars['block']) && isset($child->vars['subblock'])) {
                        $subBlock = $formConfig->getSubBlocks($child->vars['block'], $child->vars['subblock']);
                        $subBlock->addData($env->render('{{ form_row(' . $formVariableName  . sprintf(
                            '.children[\'%s\'].children[\'%s\']',
                            $formChildName, $childName
                        ) . ') }}', $context));
                    }
                }
            }

            $env->setLoader($tmpLoader);

            return $formConfig->toArray();
        }

        return array();
    }
}
