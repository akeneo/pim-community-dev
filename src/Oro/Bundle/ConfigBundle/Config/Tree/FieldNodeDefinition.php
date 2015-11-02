<?php

namespace Oro\Bundle\ConfigBundle\Config\Tree;

class FieldNodeDefinition extends AbstractNodeDefinition
{
    /**
     * Return field type
     *
     * @return string
     */
    public function getType()
    {
        return $this->definition['type'];
    }

    /**
     * Return acl resource name if defined
     *
     * @return bool|string
     */
    public function getAclResource()
    {
        if (!empty($this->definition['acl_resource'])) {
            return $this->definition['acl_resource'];
        }

        return false;
    }

    /**
     * Get field options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->definition['options'];
    }

    /**
     * Set field options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->definition['options'] = $options;

        return $this;
    }

    /**
     * Replace field option by name
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function replaceOption($name, $value)
    {
        $this->definition['options'][$name] = $value;

        return $this;
    }

    /**
     * Returns options in specific format for config form field
     *
     * @return array
     */
    public function toFormFieldOptions()
    {
        return array_merge(
            [
                'target_field' => $this
            ],
            array_intersect_key(
                $this->getOptions(),
                array_flip(['label', 'required', 'block', 'subblock', 'tooltip'])
            )
        );
    }

    /**
     * Prepare definition, set default values
     *
     * @param array $definition
     *
     * @return array
     */
    protected function prepareDefinition(array $definition)
    {
        if (!isset($definition['options'])) {
            $definition['options'] = [];
        }

        if (isset($definition['options']['constraints'])) {
            $definition['options']['constraints'] = $this->parseValidator($definition['options']['constraints']);
        }

        return parent::prepareDefinition($definition);
    }

    /**
     * @param $name
     * @param $options
     * @return mixed
     *
     * TODO: use ConstraintFactory here, https://magecore.atlassian.net/browse/BAP-2270
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
     *
     * @return array
     */
    protected function parseValidator(array $nodes)
    {
        $values = [];


        foreach ($nodes as $name => $childNodes) {
            if (is_numeric($name) && is_array($childNodes) && count($childNodes) == 1) {
                $options = current($childNodes);

                if (is_array($options)) {
                    $options = $this->parseValidator($options);
                }

                $values[] = $this->newConstraint(key($childNodes), $options);
            } else {
                $values[$name] = $childNodes;
            }
        }

        return $values;
    }
}
