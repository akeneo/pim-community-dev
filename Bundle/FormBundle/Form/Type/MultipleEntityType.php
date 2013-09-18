<?php
namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MultipleEntityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->setOptionToView($view, $options, 'grid_url');
        $this->setOptionToView($view, $options, 'initial_elements');
        $this->setOptionToView($view, $options, 'selector_window_title');
        $this->setOptionToView($view, $options, 'default_element');
    }

    /**
     * @param FormView $view
     * @param array $options
     * @param string $option
     */
    protected function setOptionToView(FormView $view, array $options, $option)
    {
        $view->vars[$option] = isset($options[$option]) ? $options[$option] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_multiple_entity';
    }
}
