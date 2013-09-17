<?php
namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MultipleEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'added',
                'oro_entity_identifier',
                array(
                    'class'    => $options['class'],
                    'multiple' => true
                )
            )
            ->add(
                'removed',
                'oro_entity_identifier',
                array(
                    'class'    => $options['class'],
                    'multiple' => true
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('class'));
        $resolver->setDefaults(
            array(
                'class' => null,
                'mapped' => false,
                'grid_url' => null,
                'default_element' => null,
                'initial_elements' => null,
                'selector_window_title' => null
            )
        );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['grid_url'] = isset($options['grid_url'])
            ? $options['grid_url']
            : null;

        $view->vars['initial_elements'] = isset($options['initial_elements'])
            ? $options['initial_elements']
            : null;

        $view->vars['selector_window_title'] = isset($options['selector_window_title'])
            ? $options['selector_window_title']
            : null;

        if (isset($options['default_element']) && $options['default_element'] instanceof FormInterface) {
            $view->vars['default_element'] =  $options['default_element']->createView($view->parent)->vars['id'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_multiple_entity';
    }
}
