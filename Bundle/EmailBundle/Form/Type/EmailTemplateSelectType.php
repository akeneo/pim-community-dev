<?php

namespace Oro\Bundle\EmailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EmailTemplateSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = function (Options $options) {
            if (empty($options['selectedEntity'])) {
                return array();
            }

            return null;
        };

        $resolver->setDefaults(
            array(
                'class'                   => 'OroEmailBundle:EmailTemplate',
                'property'                => 'name',
                'query_builder'           => null,
                'depends_on_parent_field' => 'entityName',
                'selectedEntity'          => null,
                'choices'                 => $choices,
                'configs' => array(
                    'placeholder' => 'oro.email.form.choose_template',
                ),
                'empty_value'             => '',
                'empty_data'              => null,
                'required'                => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['depends_on_parent_field'] = $form->getConfig()->getOption('depends_on_parent_field');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_email_template_list';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_translatable_entity';
    }
}
