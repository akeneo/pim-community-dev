<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Exception\AnnotationException;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Configurable
{
    public $mode = ConfigModelManager::MODE_DEFAULT;
    public $routeName = '';
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

        if (isset($data['defaultValues'])) {
            $this->defaultValues = $data['defaultValues'];
        }

        if (!is_array($this->defaultValues)) {
            throw new AnnotationException(
                sprintf(
                    'Annotation "Configurable" parameter "defaultValues" expect "array" but "%s" given',
                    gettype($this->defaultValues)
                )
            );
        }

        $availableMode = array(
            ConfigModelManager::MODE_DEFAULT,
            ConfigModelManager::MODE_HIDDEN,
            ConfigModelManager::MODE_READONLY
        );

        if (!in_array($this->mode, $availableMode)) {
            throw new AnnotationException(
                sprintf('Annotation "Configurable" give invalid parameter "mode" : "%s"', $this->mode)
            );
        }
    }
}
