<?php

namespace Oro\Bundle\EntityBundle\Metadata\Driver;

use Metadata\Driver\DriverInterface;

use Oro\Bundle\EntityBundle\Metadata\AuditEntityMetadata;
use Oro\Bundle\EntityBundle\Metadata\AuditFieldMetadata;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class AuditConfigDriver implements DriverInterface
{
    /**
     * @var ConfigProvider
     */
    protected $auditConfigProvider;

    public function __construct(ConfigProvider $auditConfigProvider)
    {
        $this->auditConfigProvider = $auditConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $metadata = new AuditEntityMetadata($class->getName());

        if ($this->auditConfigProvider->hasConfig($class->getName())
            && $this->auditConfigProvider->getConfig($class->getName())->is('auditable')
        ) {
            $metadata = new AuditEntityMetadata($class->getName());

            foreach ($class->getProperties() as $reflectionProperty) {
                if ($this->auditConfigProvider->hasFieldConfig($class->getName(), $reflectionProperty->getName())
                    && ($fieldConfig = $this->auditConfigProvider->getFieldConfig($class->getName(), $reflectionProperty->getName()))
                    && $fieldConfig->is('auditable')
                ) {
                    $propertyMetadata = new AuditFieldMetadata($class->getName(), $reflectionProperty->getName());

                    $propertyMetadata->auditable = $fieldConfig->get('auditable');

                    $metadata->addPropertyMetadata($propertyMetadata);
                }
            }

            if (count($metadata->propertyMetadata)) {
                return $metadata->auditable = true;
            }
        }

        return $metadata;
    }
}
