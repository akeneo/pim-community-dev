<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;

use Oro\Bundle\EntityConfigBundle\Exception\AnnotationException;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Config
{
    public $mode = ConfigModelManager::MODE_DEFAULT;
    public $routeName = '';
    public $routeView = '';
    public $defaultValues = array();

    public function __construct(array $data)
    {
        if (isset($data['mode'])) {
            $this->mode = $data['mode'];
        } elseif (isset($data['value'])) {
            $this->mode = $data['value'];
        }

        if (isset($data['routeName'])) {
            $this->routeName = $data['routeName'];
        }

        if (isset($data['routeView'])) {
            $this->routeView = $data['routeView'];
        }

        if (isset($data['defaultValues'])) {
            $this->defaultValues = $data['defaultValues'];
        }

        if (!is_array($this->defaultValues)) {
            throw new AnnotationException(
                sprintf(
                    'Annotation "Config" parameter "defaultValues" expect "array" but "%s" given',
                    gettype($this->defaultValues)
                )
            );
        }

        $availableMode = array(
            ConfigModelManager::MODE_DEFAULT,
            ConfigModelManager::MODE_READONLY
        );

        if (!in_array($this->mode, $availableMode)) {
            throw new AnnotationException(
                sprintf('Annotation "Config" give invalid parameter "mode" : "%s"', $this->mode)
            );
        }
    }
}
