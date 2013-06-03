<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityTransformerInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OroJquerySelect2HiddenType extends AbstractType
{
    /**
     * @var EntityToIdTransformer
     */
    protected $entityTransformer;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityTransformerInterface $entityTransformer, EntityManager $em)
    {
        $this->entityTransformer = $entityTransformer;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('autocompleter_alias', 'class'));
        $resolver->setDefaults(
            array(
                'empty_value' => '',
                'empty_data' => null,
                'data_class' => null
            )
        );
    }

    /**
     * Prepare entity transformer and unset data_class to support autosuggestion.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modelTransformer = new EntityToIdTransformer($this->em, $options['class']);
        $builder->addModelTransformer($modelTransformer);

        parent::buildForm($builder, $options);
    }

    /**
     * Set data-title attribute to element to show selected value
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $title = $this->entityTransformer->transform($options['autocompleter_alias'], $form->getData());
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
