<?php

namespace Oro\Bundle\FormBundle\DataTransformer;

class EntityToTextTransformer implements EntityTransformerInterface
{
    protected $configuration;

//    public function __construct($configuration)
//    {
//        $this->configuration = $configuration;
//    }

    public function transform($alias, $value)
    {
        // TODO: get fields from configuration
        $fields = array('name');

        $data = array();
        foreach ($fields as $field) {
            $data[] = $this->getPropertyValue($field, $value);
        }
        return implode(' ', $data);
    }

    protected function getPropertyValue($name, $object)
    {
        $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
        if (method_exists($object, $method)) {
            return $object->$method();
        }
        return '';
    }
}
