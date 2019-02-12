<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Loader;

use Doctrine\Common\Annotations\AnnotationReader;
use Oro\Bundle\SecurityBundle\Annotation\Loader\AclAnnotationLoader;
use Oro\Bundle\SecurityBundle\Annotation\Loader\AclYamlConfigLoader;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Fixtures\Classes\ConfigController;
use Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Fixtures\TestBundle;

class AclAnnotationLoadersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp(): void
    {
        if (!interface_exists('Doctrine\Common\Annotations\Reader')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }
    }

    public function testLoaders()
    {
        $storage = new AclAnnotationStorage();
        $annotationLoader = new AclAnnotationLoader(
            ['TestBundle' => TestBundle::class],
            [],
            new AnnotationReader()
        );
        $annotationLoader->load($storage);
        $yamlConfigLoader = new AclYamlConfigLoader(
            ['TestBundle' => TestBundle::class]
        );
        $yamlConfigLoader->load($storage);

        $a = $storage->findById('user_test_main_controller');
        $this->assertNotNull($a);
        $this->assertEquals('user_test_main_controller', $a->getId());
        $this->assertEquals('action', $a->getType());
        $this->assertEquals('', $a->getPermission());
        $this->assertEquals('Test Group', $a->getGroup());
        $this->assertEquals('Test controller for ACL', $a->getLabel());
        $a = $storage->find('Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Fixtures\Classes\MainTestController');
        $this->assertNotNull($a);
        $this->assertEquals('user_test_main_controller', $a->getId());

        $a = $storage->findById('user_test_main_controller_action1');
        $this->assertNotNull($a);
        $this->assertEquals('user_test_main_controller_action1', $a->getId());
        $this->assertEquals('entity', $a->getType());
        $this->assertEquals('AcmeBundle\Entity\SomeClass', $a->getClass());
        $this->assertEquals('VIEW', $a->getPermission());
        $this->assertEquals('Test Group', $a->getGroup());
        $this->assertEquals('Action 1', $a->getLabel());
        $a = $storage->find(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Fixtures\Classes\MainTestController',
            'test1Action'
        );
        $this->assertNotNull($a);
        $this->assertEquals('user_test_main_controller_action1', $a->getId());

        $a = $storage->findById('user_test_main_controller_action2');
        $this->assertNotNull($a);
        $this->assertEquals('user_test_main_controller_action2', $a->getId());
        $this->assertEquals('action', $a->getType());
        $this->assertEquals('', $a->getPermission());
        $this->assertEquals('Another Group', $a->getGroup());
        $this->assertEquals('Action 2', $a->getLabel());
        $a = $storage->find(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Fixtures\Classes\MainTestController',
            'test2Action'
        );
        $this->assertEquals('user_test_main_controller_action2', $a->getId());
        $a = $storage->find(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Fixtures\Classes\MainTestController',
            'test3Action'
        );
        $this->assertNotNull($a);
        $this->assertEquals('user_test_main_controller_action2', $a->getId());

        $a = $storage->findById('test_controller');
        $this->assertNotNull($a);
        $this->assertEquals('test_controller', $a->getId());
        $this->assertEquals('entity', $a->getType());
        $this->assertEquals('AcmeBundle\Entity\SomeEntity', $a->getClass());
        $this->assertEquals('VIEW', $a->getPermission());
        $this->assertEquals('Test Group', $a->getGroup());
        $this->assertEquals('Test controller', $a->getLabel());
        $a = $storage->find(
            ConfigController::class,
            'testAction'
        );
        $this->assertNotNull($a);
        $this->assertEquals('test_controller', $a->getId());

        $a = $storage->findById('test_wo_bindings');
        $this->assertNotNull($a);
        $this->assertEquals('test_wo_bindings', $a->getId());
        $this->assertEquals('action', $a->getType());
        $this->assertEquals('', $a->getPermission());
        $this->assertEquals('Another Group', $a->getGroup());
        $this->assertEquals('Test without bindings', $a->getLabel());

        $this->assertCount(5, $storage->getAnnotations());
        $this->assertCount(2, $storage->getAnnotations('entity'));
        $this->assertCount(3, $storage->getAnnotations('action'));
    }
}
