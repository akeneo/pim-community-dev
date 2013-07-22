<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfig;
use Oro\Bundle\EntityConfigBundle\Exception\AnnotationException;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Configurable
{
    public $viewMode = AbstractConfig::MODE_VIEW_DEFAULT;

    public function __construct(array $data)
    {
        if (isset($data['viewMode'])) {
            $this->viewMode = $data['viewMode'];
        } elseif (isset($data['value'])) {
            $this->viewMode = $data['value'];
        }

        if (!in_array($this->viewMode, array(AbstractConfig::MODE_VIEW_DEFAULT, AbstractConfig::MODE_VIEW_HIDDEN, AbstractConfig::MODE_VIEW_READONLY))) {
            throw new AnnotationException(sprintf('Annotation "Configurable" give invalid parameter "viewMode" : "%s"', $this->viewMode));
        }
    }
}
