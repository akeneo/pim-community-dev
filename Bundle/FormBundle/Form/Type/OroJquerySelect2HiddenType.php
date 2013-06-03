<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Oro\Bundle\FormBundle\DataTransformer\EntityTransformerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OroJquerySelect2HiddenType extends AbstractType
{
    /**
     * @var EntityTransformerInterface
     */
    protected $transformer;

    public function __construct(EntityTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('autocompleter_alias'));
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'Choose...'
                ),
                'empty_value' => '',
                'empty_data'  => null,
                'data_class' => null
            )
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $title = $this->transformer->transform($options['autocompleter_alias'], $form->getData());
        $view->vars = array_replace_recursive(
            $view->vars,
            array(
                'attr' => array('data-title' => $title)
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
        return 'oro_jqueryselect2_hidden';
    }
}
