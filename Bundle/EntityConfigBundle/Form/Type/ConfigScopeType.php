<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel;

class ConfigScopeType extends AbstractType
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var AbstractConfigModel
     */
    protected $configModel;

    /**
     * @param $items
     * @param $config
     * @param $configModel
     * @param $configManager
     */
    public function __construct($items, ConfigInterface $config, ConfigManager $configManager, AbstractConfigModel $configModel)
    {
        $this->items         = $items;
        $this->config        = $config;
        $this->configModel   = $configModel;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->items as $code => $config) {
            if (isset($config['form']['type'])) {
                if (isset($config['options']['required_property'])) {
                    $property = $config['options']['required_property'];

                    if (isset($property['config_id'])) {
                        $configId = $property['config_id'];

                        $fieldName = isset($configId['field_name']) ? $configId['field_name'] : null;
                        if (!$fieldName && $this->config->getConfigId() instanceof FieldConfigId) {
                            $fieldName = $this->config->getConfigId()->getFieldName();
                        }

                        $className = isset($configId['class_name'])
                            ? $configId['class_name']
                            : $this->config->getConfigId()->getClassName();

                        $scope = isset($configId['scope'])
                            ? $configId['scope']
                            : $this->config->getConfigId()->getScope();

                        if ($fieldName) {
                            $configId = new FieldConfigId($className, $scope, $fieldName);
                        } else {
                            $configId = new EntityConfigId($className, $scope);
                        }
                    } else {
                        $configId = $this->config->getConfigId();
                    }

                    $requireConfig = $this->configManager->getConfig($configId);
                    if ($requireConfig->get($property['code']) != $property['value']) {
                        continue;
                    }
                }

                $options = isset($config['form']['options']) ? $config['form']['options'] : array();

                if (isset($config['constraints'])) {
                    $options['constraints'] = $this->parseValidator($config['constraints']);
                }

                $builder->add($code, $config['form']['type'], $options);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_config_scope_type';
    }

    /**
     * @param $name
     * @param $options
     * @return mixed
     */
    protected function newConstraint($name, $options)
    {
        if (strpos($name, '\\') !== false && class_exists($name)) {
            $className = (string)$name;
        } else {
            $className = 'Symfony\\Component\\Validator\\Constraints\\' . $name;
        }

        return new $className($options);
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function parseValidator(array $nodes)
    {
        $values = array();

        foreach ($nodes as $name => $childNodes) {
            if (is_numeric($name) && is_array($childNodes) && count($childNodes) == 1) {
                $options = current($childNodes);

                if (is_array($options)) {
                    $options = $this->parseValidator($options);
                }

                $values[] = $this->newConstraint(key($childNodes), $options);
            } else {
                if (is_array($childNodes)) {
                    $childNodes = $this->parseValidator($childNodes);
                }

                $values[$name] = $childNodes;
            }
        }

        return $values;
    }
}
