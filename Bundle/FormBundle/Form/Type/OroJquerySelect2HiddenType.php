<?php

namespace Oro\Bundle\FormBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\DataTransformerInterface;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
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
            'extra_config'            => 'autocomplete',
            'properties'              => null,
            'selection_template_twig' => null,
            'result_template_twig'    => null,
            'placeholder'             => 'Choose a value...',
            'allowClear'              => true,
            'minimumInputLength'      => 1,
            'ajax'                    => array('url' => null)
        );

        $searchRegistry = $this->searchRegistry;
        $formType = $this;

        $resolver
            ->setDefaults(
                array(
                    'empty_value'    => '',
                    'empty_data'     => null,
                    'data_class'     => null,
                    'configs'        => $defaultConfig,
                    'search_handler' => null,
                    'route_name'     => 'oro_form_autocomplete_search',
                    'url'            => null
                )
            )
            ->setRequired(
                array(
                    'autocomplete_alias'
                )
            )
            ->setNormalizers(
                array(
                    'search_handler' => function (Options $options, $value) use ($searchRegistry) {
                        if (!$value) {
                            $autocompleteAlias = $options['autocomplete_alias'];
                            if (!$searchRegistry->hasSearchHandler($autocompleteAlias)) {
                                throw new FormException(
                                    sprintf(
                                        'The option "autocomplete_alias" references to not registered autocomplete '
                                        . 'search handler "%s".',
                                        $autocompleteAlias
                                    )
                                );
                            }
                            $value = $searchRegistry->getSearchHandler($autocompleteAlias);
                        } elseif (!$value instanceof SearchHandlerInterface) {
                            throw new FormException(
                                sprintf(
                                    'The option "search_handler" must be an instance of "%s".',
                                    'Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface'
                                )
                            );
                        }
                        return $value;
                    },
                    'configs' => function (Options $options, $configs) use ($defaultConfig) {
                        $result = array_replace_recursive($defaultConfig, $configs);
                        $result['autocomplete_alias'] = $options['autocomplete_alias'];

                        $result['properties'] = $options['search_handler']->getProperties();
                        $result['route_name'] = $options['route_name'];

                        if (!empty($options['url'])) {
                            $result = array_replace_recursive(
                                $result,
                                array('ajax' => array('url' => $options['url']))
                            );
                        }

                        return $result;
                    },
                    'transformer' => function (Options $options, $value) use ($formType) {
                        if (!$value) {
                            $value = $formType->createDefaultTransformer($options['search_handler']);
                        }
                        if (!$value instanceof DataTransformerInterface) {
                            throw new FormException(
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
     * @param SearchHandlerInterface $searchHandler
     * @return EntityToIdTransformer
     */
    public function createDefaultTransformer(SearchHandlerInterface $searchHandler)
    {
        return $value = new EntityToIdTransformer($this->entityManager, $searchHandler->getEntityName());
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

        $vars = array('configs' => $options['configs']);
        if ($form->getData()) {
            $vars['attr'] = array(
                'data-entity' => json_encode($options['search_handler']->convertItem($form->getData()))
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
