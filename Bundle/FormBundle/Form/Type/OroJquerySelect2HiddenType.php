<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\EntityAutocomplete\Configuration;
use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityPropertiesTransformer;
use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityTransformerInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class OroJquerySelect2HiddenType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(EntityManager $em, Configuration $configuration)
    {
        $this->em = $em;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaults = array(
            'allowClear'         => true,
            'minimumInputLength' => 1,
        );
        $resolver
            ->setNormalizers(
                array(
                    'configs' => function (Options $options, $configs) use ($defaults) {
                        return array_merge_recursive($defaults, $configs);
                    },
                )
            )
            ->setDefaults(
                array(
                    'empty_value' => '',
                    'empty_data' => null,
                    'data_class' => null,
                    'autocomplete_transformer' => null,
                    'configs' => $defaults
                )
            );
    }

    /**
     * Prepare entity transformer and unset data_class to support autosuggestion.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (array_key_exists('autocomplete_alias', $options)) {
            $autocompleteOptions = $this->configuration->getAutocompleteOptions($options['autocomplete_alias']);
            $entityClass = $autocompleteOptions['entity_class'];
        } elseif (array_key_exists('entity_class', $options)) {
            $entityClass = $options['entity_class'];
        } else {
            throw new MissingOptionsException('Option "autocomplete_alias" or "entity_class" must be defined.');
        }
        $modelTransformer = new EntityToIdTransformer($this->em, $entityClass);
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

        $configs = $this->getConfigs($options);

        $vars = array('configs' => $configs);
        if ($form->getData()) {
            $vars['attr'] = array(
                'data-entity' => $this->encodeEntity(
                    $form->getData(),
                    $configs['properties'],
                    $options['autocomplete_transformer']
                )
            );
        }
        $view->vars = array_replace_recursive($view->vars, $vars);
    }

    /**
     * Prepare required options based on autocomplete configuration.
     *
     * @param array $options
     * @return array
     * @throws MissingOptionsException
     */
    protected function getConfigs($options)
    {
        if (array_key_exists('autocomplete_alias', $options)) {
            $autocompleteOptions = $this->configuration->getAutocompleteOptions($options['autocomplete_alias']);
            $configs = array_key_exists('form_options', $autocompleteOptions) ? $autocompleteOptions['form_options'] : array();
            if (isset($autocompleteOptions['route'])) {
                $configs['route'] = $autocompleteOptions['route'];
            }

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
        } elseif (array_key_exists('configs', $options)) {
            $configs = $options['configs'];
        } else {
            $configs = array();
        }

        if (array_key_exists('properties', $configs)) {
            if (!is_array($configs['properties'])) {
                $configs['properties'] = array($configs['properties']);
            }
        } else {
            $configs['properties'] = array();
        }

        if (!array_key_exists('minimumInputLength', $configs)) {
            $configs['minimumInputLength'] = 1;
        }
        if (!array_key_exists('allowClear', $configs)) {
            $configs['allowClear'] = true;
        }

        return $configs;
    }

    /**
     * @param mixed $entity
     * @param array $properties
     * @param EntityTransformerInterface|null $entityTransformer
     * @return string
     */
    protected function encodeEntity($entity, array $properties, EntityTransformerInterface $entityTransformer = null)
    {
        if (null == $entityTransformer) {
            $entityTransformer = new EntityPropertiesTransformer($properties);
        }
        return json_encode($entityTransformer->transform($entity));
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
