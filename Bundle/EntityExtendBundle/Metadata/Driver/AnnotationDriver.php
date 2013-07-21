<?php

namespace Oro\Bundle\EntityExtendBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;

use Metadata\Driver\DriverInterface;

use Oro\Bundle\EntityExtendBundle\Metadata\ExtendClassMetadata;

class AnnotationDriver implements DriverInterface
{
    /**
     * Annotation reader uses a full class pass for parsing
     */
    const EXTEND = 'Oro\\Bundle\\EntityExtendBundle\\Metadata\\Annotation\\Extend';

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
        $metadata = new ExtendClassMetadata($class->getName());

        if ($this->reader->getClassAnnotation($class, self::EXTEND)) {
            $metadata->isExtend = true;
        }

        return $metadata;
    }
}
