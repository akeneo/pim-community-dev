<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Doctrine\Common\Inflector\Inflector;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Router;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class CustomEntityType extends AbstractType
{
    const NAME = 'custom_entity_type';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected $typeMap = array(
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
    );

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
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $className = $options['class_name'];
        $data      = $builder->getData();

        /** @var ConfigProvider $formConfigProvider */
        $formConfigProvider = $this->configManager->getProvider('form');
        $formConfigs        = $formConfigProvider->getConfigs($className);

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->configManager->getProvider('entity');

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');

        foreach ($formConfigs as $formConfig) {
            // TODO: refactor ConfigIdInterface to allow extracting of field name,
            // TODO: should be done in scope https://magecore.atlassian.net/browse/BAP-1722
            $extendConfig = $extendConfigProvider->getConfig($className, $formConfig->getId()->getFieldName());

            // TODO: Convert this check to method in separate helper service and reuse it in ExtendEntityExtension,
            // TODO: should be done in scope of https://magecore.atlassian.net/browse/BAP-1721
            if ($formConfig->get('is_enabled')
                && !$extendConfig->is('is_deleted')
                && $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                && !$extendConfig->is('state', ExtendManager::STATE_NEW)
                && !in_array($formConfig->getId()->getFieldType(), array('ref-one', 'ref-many'))
            ) {
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId = $formConfig->getId();

                $entityConfig = $entityConfigProvider->getConfig(
                    $fieldConfigId->getClassName(),
                    $fieldConfigId->getFieldName()
                );

                $options = array(
                    'label'    => $entityConfig->get('label'),
                    'required' => false,
                    'block'    => 'general',
                );

                switch ($fieldConfigId->getFieldType()) {
                    case 'boolean':
                        $options['empty_value'] = false;
                        $options['choices']     = array('No', 'Yes');
                        break;
                    case 'manyToOne':
                        $options['entity_class'] = $extendConfig->get('target_entity');
                        $options['configs']      = array(
                            'placeholder'   => 'oro.form.choose_value',
                            'extra_config'  => 'relation',
                            'target_entity' => str_replace('\\', '_', $extendConfig->get('target_entity')),
                            'target_field'  => $extendConfig->get('target_field'),
                            'properties'    => array($extendConfig->get('target_field')),
                        );
                        break;
                    case 'oneToMany':
                    case 'manyToMany':
                        $classArray          = explode('\\', $extendConfig->get('target_entity'));
                        $blockName           = array_pop($classArray);
                        $selectorWindowTitle = 'Select ' . $blockName;

                        $builder->add(
                            'default_' . $fieldConfigId->getFieldName(),
                            'oro_entity_identifier',
                            array(
                                'class'    => $extendConfig->get('target_entity'),
                                'multiple' => false
                            )
                        );

                        $options = array(
                            'label'                 => false,
                            'required'              => false,
                            'block'                 => $blockName,
                            'block_config'          => array(
                                $blockName => array(
                                    'title'   => null,
                                    'subblocks' => array(
                                        array(
                                            'useSpan' => false,
                                        )
                                    )
                                )
                            ),
                            'class'                 => $extendConfig->get('target_entity'),
                            'grid_url'              => $this->router->generate(
                                'oro_entity_relation',
                                array(
                                    'id'        => $data->getId() ? : 0,
                                    'className' => str_replace('\\', '_', $className),
                                    'fieldName' => $fieldConfigId->getFieldName()
                                )
                            ),
                            'selector_window_title' => $selectorWindowTitle,
                            'default_element' => 'default_' . $fieldConfigId->getFieldName(),
                            'initial_elements' => null,
                            'mapped' => false,
                            'extend' => true,
                        );

                        break;
                }

                $builder->add(
                    $fieldConfigId->getFieldName(),
                    $this->typeMap[$fieldConfigId->getFieldType()],
                    $options
                );
            }
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $blockConfig = isset($view->vars['block_config']) ? $view->vars['block_config'] : array();

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
                && !in_array($formConfig->getId()->getFieldType(), array('ref-one', 'ref-many'))
            ) {
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId = $formConfig->getId();
                if ($fieldConfigId->getFieldType() == 'oneToMany') {
                    $fieldName = $fieldConfigId->getFieldName();

                    $view->children[$fieldName]->vars['grid_url'] =
                        $this->router->generate(
                            'oro_entity_relation',
                            array(
                                'id'        => $data->getId() ? : 0,
                                'className' => str_replace('\\', '_', $className),
                                'fieldName' => $fieldName
                            )
                        );

                    $defaultFieldName = 'get_' . ExtendConfigDumper::DEFAULT_PREFIX . $fieldName;
                    $defaultEntityId  = $data->{Inflector::camelize($defaultFieldName)}();

                    $classArray = explode('\\', $className);
                    $relatedFieldName =
                        ExtendConfigDumper::FIELD_PREFIX
                        . strtolower(array_pop($classArray)) . '_'
                        . $fieldName;

                    $selectedCollection = $this->configManager->getEntityManager()
                        ->getRepository($extendConfig->get('target_entity'))
                        ->findBy(array($relatedFieldName => $data->getId()));

                    $view->children[$fieldName]->vars['initial_elements'] =
                        $this->getInitialElements($selectedCollection, $defaultEntityId, $extendConfig);
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
        $result = array();
        foreach ($entities as $entity) {
            $extraData = array();
            foreach ($extendConfig->get('target_view') as $fieldName) {
                $label =$this->configManager->getProvider('entity')
                    ->getConfig($extendConfig->get('target_entity'), $fieldName)
                    ->get('label');

                $extraData[] = array(
                    'label' => $label,
                    'value' => $entity->{Inflector::camelize('get_' . $fieldName)}()
                );
            }

            $result[] = array(
                'id' => $entity->getId(),
                'label' => $entity->{Inflector::camelize('get_' . $extendConfig->get('target_title'))}(),
                'link' => $this->router->generate(
                    'oro_entity_detailed',
                    array(
                        'id' => $entity->getId(),
                        'className' => str_replace('\\', '_', $extendConfig->getId()->getClassName()),
                        'fieldName' => $extendConfig->getId()->getFieldName()
                    )
                ),
                'extraData' => $extraData,
                'isDefault' => ($default != null && $default->getId() == $entity->getId())

            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('class_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
