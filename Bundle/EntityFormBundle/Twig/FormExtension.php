<?php

namespace Oro\Bundle\EntityFormBundle\Twig;

use Symfony\Component\Form\FormView;

class FormExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
//            new \Twig_SimpleFunction('form_data_blocks', function (\Twig_Environment $env, $context, FormView $form) {
//                $tmpLoader = $env->getLoader();
//                $env->setLoader(new \Twig_Loader_String());
//
//                $tt = $env->render("{{ form_widget(form)}}", $context);
//                var_dump($tt);
//
//                $env->setLoader($tmpLoader);
//                //die;
//            }, array('needs_context' => true, 'needs_environment' => true))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_form';
    }
}
