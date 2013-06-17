<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;

use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;

class AnnotationDriver implements DriverInterface
{
    /**
     * Annotation reader uses a full class pass for parsing
     */
    const CONFIGURABLE = 'Oro\\Bundle\\EntityConfigBundle\\Metadata\\Annotation\\Configurable';

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
        return $this->reader->getClassAnnotation($class, self::CONFIGURABLE) ? new ClassMetadata($class->getName()) : null;
    }
}
