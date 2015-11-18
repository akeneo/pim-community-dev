<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Annotation;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

class AclTest extends \PHPUnit_Framework_TestCase
{
    public function testAnnotation()
    {
        $annotation = new Acl(
            [
                'id'               => 'test_acl',
                'type'             => 'SomeType',
                'class'            => 'SomeClass',
                'permission'       => 'SomePermission',
                'group_name'       => 'SomeGroup',
                'label'            => 'SomeLabel',
                'ignore_class_acl' => true
            ]
        );
        $this->assertEquals('test_acl', $annotation->getId());
        $this->assertEquals('SomeType', $annotation->getType());
        $this->assertEquals('SomeClass', $annotation->getClass());
        $this->assertEquals('SomePermission', $annotation->getPermission());
        $this->assertEquals('SomeGroup', $annotation->getGroup());
        $this->assertEquals('SomeLabel', $annotation->getLabel());
        $this->assertTrue($annotation->getIgnoreClassAcl());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnnotationWithEmptyId()
    {
        $annotation = new Acl(['id' => '']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnnotationWithInvalidId()
    {
        $annotation = new Acl(['id' => 'test acl']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnnotationWithMissingId()
    {
        $annotation = new Acl([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnnotationWithEmptyType()
    {
        $annotation = new Acl(['id' => 'test', 'type' => '']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnnotationWithMissingType()
    {
        $annotation = new Acl(['id' => 'test']);
    }
}
