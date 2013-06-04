<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\EntityAutocomplete\Configuration;
use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
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

    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(EntityTransformerInterface $entityTransformer, EntityManager $em, Configuration $configuration)
    {
        $this->entityTransformer = $entityTransformer;
        $this->em = $em;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('autocomplete_alias'));
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
        $autocompleteOptions = $this->configuration->getAutocompleteOptions($options['autocomplete_alias']);
        $modelTransformer = new EntityToIdTransformer($this->em, $autocompleteOptions['entity_class']);
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
        $title = $this->entityTransformer->transform($options['autocomplete_alias'], $form->getData());

        // Prepare required options based on autocomplete configuration
        $autocompleteOptions = $this->configuration->getAutocompleteOptions($options['autocomplete_alias']);
        $configs = array_key_exists('form_options', $autocompleteOptions) ? $autocompleteOptions['form_options'] : array();
        $configs['route'] = $autocompleteOptions['route'];

        $properties = array();
        /** @var Property $property */
        foreach ($autocompleteOptions['properties'] as $property) {
            $properties[] = $property->getName();
        }
        $configs['properties'] = $properties;

        $configs['autocomplete_alias'] = $options['autocomplete_alias'];
        if (isset($autocompleteOptions['url'])) {
            $configs = array_replace_recursive(
                $configs,
                array(
                    'ajax' => array('url' => $autocompleteOptions['url'])
                )
            );
        }

        $view->vars = array_replace_recursive(
            $view->vars,
            array(
                'attr' => array('data-title' => $title),
                'configs' => $configs
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
