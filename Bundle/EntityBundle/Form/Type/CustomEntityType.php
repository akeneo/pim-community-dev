<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

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
                                    'id'        => $data ? $data->getId() : 0,
                                    'className' => str_replace('\\', '_', $className),
                                    'fieldName' => $fieldConfigId->getFieldName()
                                )
                            ),
                            'selector_window_title' => $selectorWindowTitle,
                            'default_element' => 'default_' . $fieldConfigId->getFieldName(),
                            'initial_elements' => null,
                            'mapped' => false,
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
