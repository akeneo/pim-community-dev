<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Metadata;

use Oro\Bundle\DataAuditBundle\Metadata\ExtendMetadataFactory;

class ExtendMetadataFactoryTest extends AbstractMetadataTest
{
    public function testExtendLoadMetadataForClass()
    {
        $doctrineClassMetadata = $this->em->getClassMetadata(
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass'
        );

        $metadata = $this->loggableAnnotationDriver->extendLoadMetadataForClass($doctrineClassMetadata);

        $metadataFactory = new ExtendMetadataFactory($this->loggableAnnotationDriver);
        $resultMetadata  = $metadataFactory->extendLoadMetadataForClass($doctrineClassMetadata);

        $metadata->createdAt = $resultMetadata->createdAt;

        $this->assertEquals($metadata, $resultMetadata);
    }
}
