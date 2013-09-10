<?php

namespace Oro\Bundle\DataAuditBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Versioned
{
    public $method;

    public function __construct(array $data)
    {
        if (isset($data['method'])) {
            $this->method = $data['method'];
        } elseif (isset($data['value'])) {
            $this->method = $data['value'];
        }
    }
}
