<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Metadata\Driver;

use Doctrine\Common\Annotations\AnnotationReader;

use Oro\Bundle\DataAuditBundle\Metadata\ClassMetadata as BaseClassMetadata;
use Oro\Bundle\DataAuditBundle\Metadata\Driver\AnnotationDriver;
use Oro\Bundle\DataAuditBundle\Metadata\PropertyMetadata;

use Oro\Bundle\DataAuditBundle\Tests\Unit\Metadata\AbstractMetadataTest;

class AnnotationDriverTest extends AbstractMetadataTest
{
    public function testExtendLoadMetadataForClass()
    {
        $doctrineClassMetadata = $this->em->getClassMetadata(
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass'
        );

        $nameProperty         = new PropertyMetadata(
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass',
            'name'
        );
        $nameProperty->method = '__toString';

        $collectionProperty               = new PropertyMetadata(
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass',
            'collection'
        );
        $collectionProperty->method       = '__toString';
        $collectionProperty->isCollection = true;

        $collectionWithMethodNameProperty = new PropertyMetadata(
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass',
            'collectionWithMethodName'
        );
        $collectionWithMethodNameProperty->isCollection = true;
        $collectionWithMethodNameProperty->method = 'getName';

        $metadata = new BaseClassMetadata('Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass');
        $metadata->addPropertyMetadata($nameProperty);
        $metadata->addPropertyMetadata($collectionProperty);
        $metadata->addPropertyMetadata($collectionWithMethodNameProperty);

        $annotationDriver = new AnnotationDriver(new AnnotationReader());

        $resultMetadata = $annotationDriver->extendLoadMetadataForClass($doctrineClassMetadata);

        $metadata->createdAt = $resultMetadata->createdAt;

        $this->assertEquals($metadata, $resultMetadata);
    }

    public function testLoadMetadataForClass()
    {
        $this->assertEquals(true, true);
    }
}
