<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Metadata;

use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor as AclAnnotationAncestor;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;

class AclAnnotationStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testStorage()
    {
        $storage = new AclAnnotationStorage();

        $storage->add(
            new AclAnnotation(['id' => 'annotation_wo_bindings', 'type' => 'entity'])
        );
        $storage->add(
            new AclAnnotation(['id' => 'annotation_with_class_bindings', 'type' => 'entity']),
            'Acme\SomeClass'
        );
        $storage->add(
            new AclAnnotation(['id' => 'annotation_with_method_bindings', 'type' => 'entity']),
            'Acme\SomeClass',
            'SomeMethod'
        );

        $storage->add(
            new AclAnnotation(['id' => 'annotation1', 'type' => 'entity'])
        );
        $storage->addAncestor(
            new AclAnnotationAncestor(['value' => 'annotation1']),
            'Acme\SomeClass1'
        );
        $storage->add(
            new AclAnnotation(['id' => 'annotation2', 'type' => 'entity'])
        );
        $storage->addAncestor(
            new AclAnnotationAncestor(['value' => 'annotation2']),
            'Acme\SomeClass1',
            'SomeMethod'
        );

        $this->assertEquals(
            'annotation_wo_bindings',
            $storage->findById('annotation_wo_bindings')->getId()
        );
        $this->assertEquals(
            'annotation_with_class_bindings',
            $storage->findById('annotation_with_class_bindings')->getId()
        );
        $this->assertEquals(
            'annotation_with_class_bindings',
            $storage->find('Acme\SomeClass')->getId()
        );
        $this->assertEquals(
            'annotation_with_method_bindings',
            $storage->findById('annotation_with_method_bindings')->getId()
        );
        $this->assertEquals(
            'annotation_with_method_bindings',
            $storage->find('Acme\SomeClass', 'SomeMethod')->getId()
        );
        $this->assertEquals(
            'annotation1',
            $storage->findById('annotation1')->getId()
        );
        $this->assertEquals(
            'annotation1',
            $storage->find('Acme\SomeClass1')->getId()
        );
        $this->assertEquals(
            'annotation2',
            $storage->findById('annotation2')->getId()
        );
        $this->assertEquals(
            'annotation2',
            $storage->find('Acme\SomeClass1', 'SomeMethod')->getId()
        );

        // test 'has' method
        $this->assertTrue($storage->has('Acme\SomeClass'));
        $this->assertFalse($storage->has('Acme\UnknownClass'));
        $this->assertTrue($storage->has('Acme\SomeClass', 'SomeMethod'));
        $this->assertFalse($storage->has('Acme\SomeClass', 'UnknownMethod'));
        $this->assertFalse($storage->has('Acme\UnknownClass', 'SomeMethod'));

        // test annotation override
        $this->assertEquals(
            'entity',
            $storage->findById('annotation2')->getType()
        );
        $storage->add(
            new AclAnnotation(['id' => 'annotation2', 'type' => 'action'])
        );
        $this->assertEquals(
            'action',
            $storage->findById('annotation2')->getType()
        );

        // test duplicate bindings
        $storage->addAncestor(
            new AclAnnotationAncestor(['value' => 'annotation2']),
            'Acme\SomeClass1',
            'SomeMethod'
        );
        $this->setExpectedException('\RuntimeException');
        $storage->addAncestor(
            new AclAnnotationAncestor(['value' => 'annotation1']),
            'Acme\SomeClass1',
            'SomeMethod'
        );
    }

    public function testSerialization()
    {
        $storage = new AclAnnotationStorage();
        $storage->add(
            new AclAnnotation(['id' => 'annotation', 'type' => 'entity']),
            'Acme\SomeClass',
            'SomeMethod'
        );
        $this->assertEquals('annotation', $storage->findById('annotation')->getId());
        $this->assertEquals('annotation', $storage->find('Acme\SomeClass', 'SomeMethod')->getId());

        $data = serialize($storage);
        $storage = unserialize($data);
        $this->assertEquals('annotation', $storage->findById('annotation')->getId());
        $this->assertEquals('annotation', $storage->find('Acme\SomeClass', 'SomeMethod')->getId());
    }
}
