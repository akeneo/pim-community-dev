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
                'grid_url' => null
            )
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['grid_url'])) {
            $view->vars['grid_url'] = $options['grid_url'];
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
