<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntitySelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $vars = array('configs' => $options['configs']);
        if ($form->getData()) {
            $vars['attr'] = array(
                'data-entities' => json_encode(array(array('id' => $form->getData(), 'text' => $form->getData())))
            );
        }

        $view->vars = array_replace_recursive($view->vars, $vars);
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = function (FormEvent $event) {
            $form = $event->getForm();
            $data = $form->getParent()->getData();
        };

        $builder->addEventListener(FormEvents::POST_SET_DATA, $event);
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
//            array(
//                'placeholder' => 'oro.form.choose_value',
//                'allowClear'  => true,
//                'configs'     => array(
//                    'placeholder'        => 'oro.form.choose_value',
//                    'extra_config'       => 'autocomplete',
//                    'route_name'         => 'oro_entity_search',
//                    'autocomplete_alias' => 'entity_select',
//                    'properties'         => array('id', 'text')
//                )
//            )
            array(
                'placeholder'        => 'oro.form.choose_value',
                'allowClear'         => true,
                'configs'            => array(
                    'placeholder'             => 'oro.form.choose_value',
                    'extra_config'            => 'autocomplete',
                    'route_name'              => 'oro_entity_search',
                    //'autocomplete_alias'      => 'entity_select',
                    'properties'              => array('id', 'text')
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_select_type';
    }
}
