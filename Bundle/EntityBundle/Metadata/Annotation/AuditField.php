<?php

namespace Oro\Bundle\EntityBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Oro\Bundle\EntityBundle\Audit\AuditManager;
use Oro\Bundle\EntityBundle\Exception\AnnotationException;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class AuditField
{
    public $commitLevel = AuditManager::COMMIT_LEVEL_BASE;

    public function __construct(array $data)
    {
        if (isset($data['commitLevel'])) {
            $this->commitLevel = $data['commitLevel'];
        } elseif (isset($data['value'])) {
            $this->commitLevel = $data['value'];
        }

        if ($this->commitLevel
            && !in_array($this->commitLevel, array(AuditManager::COMMIT_LEVEL_BASE, AuditManager::COMMIT_LEVEL_ADVANCED))
        ) {
            throw new AnnotationException(sprintf('"commitLevel" should be "%s" or "%s" given "%s"',
                AuditManager::COMMIT_LEVEL_BASE, AuditManager::COMMIT_LEVEL_ADVANCED, $this->commitLevel
            ));
        }
    }
}
