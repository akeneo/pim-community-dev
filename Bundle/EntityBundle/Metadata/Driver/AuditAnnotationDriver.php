<?php

namespace Oro\Bundle\EntityBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;

use Metadata\Driver\DriverInterface;

use Oro\Bundle\EntityBundle\Metadata\AuditEntityMetadata;
use Oro\Bundle\EntityBundle\Metadata\AuditFieldMetadata;

class AuditAnnotationDriver implements DriverInterface
{
    /**
     * Annotation reader uses a full class pass for parsing
     */
    const AUDIT_ENTITY = 'Oro\\Bundle\\EntityBundle\\Metadata\\Annotation\\AuditEntity';
    const AUDIT_FIELD  = 'Oro\\Bundle\\EntityBundle\\Metadata\\Annotation\\AuditField';

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $metadata = new AuditEntityMetadata($class->getName());

        if ($this->reader->getClassAnnotation($class, self::AUDIT_ENTITY)) {
            $metadata->auditable = true;
        }

        foreach ($class->getProperties() as $field) {
            if ($annotation = $this->reader->getPropertyAnnotation($field, self::AUDIT_FIELD)) {
                $fieldMeta = new AuditFieldMetadata($class, $field->getName());

                $fieldMeta->auditable = $annotation->commitLevel;

                $metadata->addPropertyMetadata($fieldMeta);
            }
        }

        if ($metadata->auditable && count($metadata->propertyMetadata)) {
            return $metadata;
        }

        return null;
    }
}
