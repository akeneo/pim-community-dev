<?php
namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OroIconType extends AbstractType
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'placeholder'        => 'oro.form.choose_value',
                'allowClear'         => true,
                'configs'            => array(
                    'placeholder'             => 'oro.form.choose_value',
                    'result_template_twig'    => 'OroFormBundle:Autocomplete:icon/result.html.twig',
                    'selection_template_twig' => 'OroFormBundle:Autocomplete:icon/selection.html.twig',
                    'extra_config'            => 'autocomplete',
                    'route_name'              => 'oro_form_autocomplete_config',
                    'autocomplete_alias'      => 'config_icon',
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
        return 'oro_icon_select';
    }
}
