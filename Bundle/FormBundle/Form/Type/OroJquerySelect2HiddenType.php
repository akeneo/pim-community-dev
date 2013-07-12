<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\DataTransformerInterface;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\FormBundle\Autocomplete\ConverterInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;

class OroJquerySelect2HiddenType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SearchRegistry
     */
    protected $searchRegistry;

    public function __construct(EntityManager $entityManager, SearchRegistry $registry)
    {
        $this->entityManager = $entityManager;
        $this->searchRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaultConfig = array(
            'placeholder'        => 'oro.form.choose_value',
            'allowClear'         => true,
            'minimumInputLength' => 1,
        );

        $searchRegistry = $this->searchRegistry;
        $formType = $this;

        $resolver
            ->setDefaults(
                array(
                    'empty_value'        => '',
                    'empty_data'         => null,
                    'data_class'         => null,
                    'entity_class'       => null,
                    'configs'            => $defaultConfig,
                    'converter'          => null,
                    'autocomplete_alias' => null
                )
            );

        $this->setConverterNormalizer($resolver);
        $this->setConfigsNormalizer($resolver, $defaultConfig);

        $resolver
            ->setNormalizers(
                array(
                    'entity_class' => function (Options $options, $value) use ($searchRegistry) {
                        if (!$value && !empty($options['autocomplete_alias'])) {
                            $searchHandler = $searchRegistry->getSearchHandler($options['autocomplete_alias']);
                            $value = $searchHandler->getEntityName();
                        }

                        if (!$value) {
                            throw new InvalidConfigurationException('The option "entity_class" must be set.');
                        }

                        return $value;
                    },
                    'transformer' => function (Options $options, $value) use ($formType) {
                        if (!$value) {
                            $value = $formType->createDefaultTransformer($options['entity_class']);
                        } elseif (!$value instanceof DataTransformerInterface) {
                            throw new TransformationFailedException(
                                sprintf(
                                    'The option "transformer" must be an instance of "%s".',
                                    'Symfony\Component\Form\DataTransformerInterface'
                                )
                            );
                        }

                        return $value;
                    }
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    protected function setConverterNormalizer(OptionsResolverInterface $resolver)
    {
        $searchRegistry = $this->searchRegistry;
        $resolver->setNormalizers(
            array(
                'converter' => function (Options $options, $value) use ($searchRegistry) {
                    if (!$value && !empty($options['autocomplete_alias'])) {
                        $value = $searchRegistry->getSearchHandler($options['autocomplete_alias']);
                    } elseif (!$value) {
                        throw new InvalidConfigurationException('The option "converter" must be set.');
                    }

                    if (!$value instanceof ConverterInterface) {
                        throw new UnexpectedTypeException(
                            $value,
                            'Oro\Bundle\FormBundle\Autocomplete\ConverterInterface'
                        );
                    }

                    return $value;
                }
            )
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     * @param array                    $defaultConfig
     */
    protected function setConfigsNormalizer(OptionsResolverInterface $resolver, array $defaultConfig)
    {
        $searchRegistry = $this->searchRegistry;
        $resolver->setNormalizers(
            array(
                'configs' => function (Options $options, $configs) use ($searchRegistry, $defaultConfig) {
                    $result = array_replace_recursive($defaultConfig, $configs);

                    if (!empty($options['autocomplete_alias'])) {
                        $result['autocomplete_alias'] = $options['autocomplete_alias'];
                        if (empty($result['properties'])) {
                            $searchHandler = $searchRegistry->getSearchHandler($options['autocomplete_alias']);
                            $result['properties'] = $searchHandler->getProperties();
                        }
                        if (empty($result['route_name'])) {
                            $result['route_name'] = 'oro_form_autocomplete_search';
                        }
                        if (empty($result['extra_config'])) {
                            $result['extra_config'] = 'autocomplete';
                        }
                    }

                    if (empty($result['route_name']) && empty($result['ajax']['url'])) {
                        throw new InvalidConfigurationException(
                            'Either option "configs.route_name" or "configs.ajax.url" must be set.'
                        );
                    }

                    return $result;
                }
            )
        );
    }

    /**
     * @param  string                $entityClass
     * @return EntityToIdTransformer
     */
    public function createDefaultTransformer($entityClass)
    {
        return $value = new EntityToIdTransformer($this->entityManager, $entityClass);
    }

    /**
     * Set data-title attribute to element to show selected value
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $vars = array('configs' => $options['configs']);
        if ($form->getData()) {
            $vars['attr'] = array(
                'data-entity' => json_encode($options['converter']->convertItem($form->getData()))
            );
        }

        $view->vars = array_replace_recursive($view->vars, $vars);
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
