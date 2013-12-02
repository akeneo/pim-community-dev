<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Doctrine\Common\Inflector\Inflector;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Router;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class CustomEntityType extends AbstractType
{
    const NAME = 'custom_entity_type';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected $typeMap = [
        'string'     => 'text',
        'integer'    => 'integer',
        'smallint'   => 'integer',
        'bigint'     => 'integer',
        'boolean'    => 'choice',
        'decimal'    => 'number',
        'date'       => 'oro_date',
        'datetime'   => 'oro_datetime',
        'text'       => 'textarea',
        'float'      => 'number',
        'manyToOne'  => 'oro_entity_select',
        'oneToMany'  => 'oro_multiple_entity',
        'manyToMany' => 'oro_multiple_entity',
        'optionSet'  => 'oro_option_select',
    ];

    /**
     * @param ConfigManager $configManager
     * @param Router $router
     */
    public function __construct(ConfigManager $configManager, Router $router)
    {
        $this->configManager = $configManager;
        $this->router        = $router;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $className = $options['class_name'];
        $data      = $builder->getData();

        /** @var ConfigProvider */
        $formConfigProvider   = $this->configManager->getProvider('form');
        $entityConfigProvider = $this->configManager->getProvider('entity');
        $extendConfigProvider = $this->configManager->getProvider('extend');

        $formConfigs = $formConfigProvider->getConfigs($className);
        foreach ($formConfigs as $formConfig) {
            // TODO: refactor ConfigIdInterface to allow extracting of field name,
            // TODO: should be done in scope https://magecore.atlassian.net/browse/BAP-1722
            $extendConfig = $extendConfigProvider->getConfig($className, $formConfig->getId()->getFieldName());

            // TODO: Convert this check to method in separate helper service and reuse it in ExtendEntityExtension,
            // TODO: should be done in scope of https://magecore.atlassian.net/browse/BAP-1721
            if ($formConfig->get('is_enabled') && !$extendConfig->is('is_deleted')
                && $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                && !$extendConfig->is('state', ExtendManager::STATE_NEW)
                && !in_array($formConfig->getId()->getFieldType(), ['ref-one', 'ref-many'])
                && !(
                    in_array($formConfig->getId()->getFieldType(), ['oneToMany', 'manyToOne', 'manyToMany'])
                    && $extendConfigProvider->getConfig($extendConfig->get('target_entity'))->is('is_deleted', true)
                )
            ) {
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId = $formConfig->getId();
                $entityConfig  = $entityConfigProvider->getConfig(
                    $fieldConfigId->getClassName(),
                    $fieldConfigId->getFieldName()
                );

                $options = [
                    'label'    => $entityConfig->get('label'),
                    'required' => false,
                    'block'    => 'general',
                ];

                switch ($fieldConfigId->getFieldType()) {
                    case 'boolean':
                        $options['empty_value'] = false;
                        $options['choices']     = ['No', 'Yes'];
                        break;
                    case 'optionSet':
                        $options['multiple'] = $extendConfig->get('set_expanded');
                        $configFieldModel    = $extendConfigProvider->getConfigManager()->getConfigFieldModel(
                            $className,
                            $fieldConfigId->getFieldName()
                        );

                        $modelOptions = $configFieldModel->getOptions()->toArray();
                        uasort(
                            $modelOptions,
                            function ($a, $b) {
                                return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
                            }
                        );

                        $options['config_id'] = $extendConfig->getId();

                        foreach ($modelOptions as $option) {
                            $options['choices'][$option->getId()] = $option->getLabel();
                            if ($option->getIsDefault()) {
                                $options['data'][] = $option->getId();
                            }
                        }

                        if ($extendConfig->is('set_expanded', false)) {
                            $options['empty_value'] = 'oro.form.choose_value';
                            if (isset($options['data']) && count($options['data'])) {
                                $options['data'] = array_shift($options['data']);
                            }
                        }
                        break;
                    case 'manyToOne':
                        $options['entity_class'] = $extendConfig->get('target_entity');
                        $options['configs']      = [
                            'placeholder'   => 'oro.form.choose_value',
                            'extra_config'  => 'relation',
                            'target_entity' => str_replace('\\', '_', $extendConfig->get('target_entity')),
                            'target_field'  => $extendConfig->get('target_field'),
                            'properties'    => [$extendConfig->get('target_field')],
                        ];
                        break;
                    case 'oneToMany':
                    case 'manyToMany':
                        $classArray          = explode('\\', $extendConfig->get('target_entity'));
                        $blockName           = array_pop($classArray);
                        $selectorWindowTitle = 'Select ' . $blockName;

                        $builder->add(
                            'default_' . $fieldConfigId->getFieldName(),
                            'oro_entity_identifier',
                            [
                                'class'    => $extendConfig->get('target_entity'),
                                'multiple' => false
                            ]
                        );

                        $options = [
                            'label'                 => $entityConfig->get('label'),
                            'required'              => false,
                            'block'                 => $blockName,
                            'block_config'          => [
                                $blockName => ['title' => null, 'subblocks' => [['useSpan' => false]]]
                            ],
                            'class'                 => $extendConfig->get('target_entity'),
                            'grid_url'              => $this->router->generate(
                                'oro_entity_relation',
                                [
                                    'id'        => (($data && $data->getId()) ? $data->getId() : 0),
                                    'className' => str_replace('\\', '_', $className),
                                    'fieldName' => $fieldConfigId->getFieldName()
                                ]
                            ),
                            'selector_window_title' => $selectorWindowTitle,
                            'default_element'       => 'default_' . $fieldConfigId->getFieldName(),
                            'initial_elements'      => null,
                            'mapped'                => false,
                            'extend'                => true,
                        ];
                        break;
                }

                $builder->add($fieldConfigId->getFieldName(), $this->typeMap[$fieldConfigId->getFieldType()], $options);
            }
        }
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $blockConfig = isset($view->vars['block_config']) ? $view->vars['block_config'] : [];

        foreach ($view->children as $child) {
            if (isset($child->vars['block_config'])) {
                $blockConfig = array_merge($blockConfig, $child->vars['block_config']);

                unset($child->vars['block_config']);
            }
        }

        $view->vars['block_config'] = $blockConfig;

        /**
         * Retrieve selected entities
         */
        $className = $options['class_name'];
        $data      = $form->getData();

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');

        /** @var ConfigProvider $formConfigProvider */
        $formConfigProvider = $this->configManager->getProvider('form');
        $formConfigs        = $formConfigProvider->getConfigs($className);
        foreach ($formConfigs as $formConfig) {
            $extendConfig = $extendConfigProvider->getConfig($className, $formConfig->getId()->getFieldName());

            if ($formConfig->get('is_enabled')
                && !$extendConfig->is('is_deleted')
                && $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                && !$extendConfig->is('state', ExtendManager::STATE_NEW)
                && !in_array($formConfig->getId()->getFieldType(), ['ref-one', 'ref-many'])
                && (
                    in_array($formConfig->getId()->getFieldType(), ['oneToMany', 'manyToOne', 'manyToMany'])
                    && $extendConfigProvider->getConfig($extendConfig->get('target_entity'))->is('is_deleted', false)
                )
            ) {
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId = $formConfig->getId();
                if (in_array($fieldConfigId->getFieldType(), ['oneToMany', 'manyToMany'])) {
                    $fieldName = $fieldConfigId->getFieldName();

                    $dataId = 0;
                    if ($data->getId()) {
                        $dataId = $data->getId();
                    }
                    $view->children[$fieldName]->vars['grid_url'] =
                        $this->router->generate(
                            'oro_entity_relation',
                            [
                                'id'        => $dataId,
                                'className' => str_replace('\\', '_', $className),
                                'fieldName' => $fieldName
                            ]
                        );

                    $defaultFieldName   = 'get_' . ExtendConfigDumper::DEFAULT_PREFIX . $fieldName;
                    $defaultEntityId    = $data->{Inflector::camelize($defaultFieldName)}();
                    $selectedCollection = $data->{Inflector::classify('get_' . $fieldName)}();

                    if ($data->getId()) {
                        $view->children[$fieldName]->vars['initial_elements'] =
                            $this->getInitialElements($selectedCollection, $defaultEntityId, $extendConfig);
                    }
                }
            }
        }
    }

    /**
     * @param $entities
     * @param $default
     * @param ConfigInterface $extendConfig
     * @return array
     */
    protected function getInitialElements($entities, $default, ConfigInterface $extendConfig)
    {
        $result = [];
        foreach ($entities as $entity) {
            $extraData = [];
            foreach ($extendConfig->get('target_grid') as $fieldName) {
                $label = $this->configManager->getProvider('entity')
                    ->getConfig($extendConfig->get('target_entity'), $fieldName)
                    ->get('label');

                $extraData[] = [
                    'label' => $label,
                    'value' => $entity->{Inflector::camelize('get_' . $fieldName)}()
                ];
            }

            $title = [];
            foreach ($extendConfig->get('target_title') as $fieldName) {
                $title[] = $entity->{Inflector::camelize('get_' . $fieldName)}();
            }

            $result[] = [
                'id'        => $entity->getId(),
                'label'     => implode(' ', $title),
                'link'      => $this->router->generate(
                    'oro_entity_detailed',
                    [
                        'id'        => $entity->getId(),
                        'className' => str_replace('\\', '_', $extendConfig->getId()->getClassName()),
                        'fieldName' => $extendConfig->getId()->getFieldName()
                    ]
                ),
                'extraData' => $extraData,
                'isDefault' => ($default != null && $default->getId() == $entity->getId())

            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['class_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
