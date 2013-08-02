<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel;
use Oro\Bundle\EntityConfigBundle\Exception\AnnotationException;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Configurable
{
    public $viewMode = AbstractConfigModel::MODE_VIEW_DEFAULT;
    public $routeName = '';
    public $defaultValues = array();

    public function __construct(array $data)
    {
        if (isset($data['viewMode'])) {
            $this->viewMode = $data['viewMode'];
        } elseif (isset($data['value'])) {
            $this->viewMode = $data['value'];
        }

        if (isset($data['routeName'])) {
            $this->routeName = $data['routeName'];
        }

        if (isset($data['defaultValues'])) {
            $this->defaultValues = $data['defaultValues'];
        }

        if (!is_array($this->defaultValues)) {
            throw new AnnotationException(
                sprintf('Annotation "Configurable" parameter "defaultValues" expect "array" but "%s" given', gettype($this->defaultValues))
            );
        }

        if (!in_array($this->viewMode, array(AbstractConfigModel::MODE_VIEW_DEFAULT, AbstractConfigModel::MODE_VIEW_HIDDEN, AbstractConfigModel::MODE_VIEW_READONLY))) {
            throw new AnnotationException(sprintf('Annotation "Configurable" give invalid parameter "viewMode" : "%s"', $this->viewMode));
        }
    }
}
