<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigScopeType extends AbstractType
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @param $items
     */
    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->items as $code => $config) {
            if (isset($config['form']['type'])) {
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
            $className = (string) $name;
        } else {
            $className = 'Symfony\\Component\\Validator\\Constraints\\'.$name;
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
