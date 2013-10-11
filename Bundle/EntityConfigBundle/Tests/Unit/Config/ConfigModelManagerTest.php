<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Tests\OrmTestCase;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\DemoEntity;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\Repository\FoundEntityConfigRepository;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\Repository\NotFoundEntityConfigRepository;

class ConfigModelManagerTest extends OrmTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ConfigModelManager
     */
    protected $configModelManager;

    public function setUp()
    {
        $this->em = $this->_getTestEntityManager();

        $this->em->getConfiguration()->setEntityNamespaces(
            array(
                'OroEntityConfigBundle' => 'Oro\\Bundle\\EntityConfigBundle\\Entity',
                'Fixture'               => 'Oro\\Bundle\\EntityConfigBundle\\Tests\\Unit\\Fixture'
            )
        );

        $reader         = new AnnotationReader;
        $metadataDriver = new AnnotationDriver(
            $reader,
            __DIR__ . '/Fixture'
        );
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($this->em));

        $this->configModelManager = new ConfigModelManager($serviceLink);

    }

    public function testGetEntityManager()
    {
        $this->assertEquals($this->em, $this->configModelManager->getEntityManager());
    }

    public function testCheckDatabaseFalse()
    {
        $schema = $this->getMockBuilder('Doctrine\Tests\Mocks\SchemaManagerMock')
            ->disableOriginalConstructor()
            ->getMock();

        $schema->expects($this->any())->method('listTableNames')->will($this->returnValue(array()));
        $this->em->getConnection()->getDriver()->setSchemaManager($schema);

        $this->assertFalse($this->configModelManager->checkDatabase());
    }

    public function testCheckDatabaseException()
    {
        $schema = $this->getMockBuilder('Doctrine\Tests\Mocks\SchemaManagerMock')
            ->disableOriginalConstructor()
            ->getMock();

        $schema->expects($this->any())->method('listTableNames')->will($this->throwException(new \PDOException()));
        $this->em->getConnection()->getDriver()->setSchemaManager($schema);

        $this->assertFalse($this->configModelManager->checkDatabase());
    }

    public function testCheckDatabase()
    {
        $schema = $this->getMockBuilder('Doctrine\Tests\Mocks\SchemaManagerMock')
            ->disableOriginalConstructor()
            ->getMock();

        $schema->expects($this->any())->method('listTableNames')->will(
            $this->returnValue(
                array(
                    'oro_entity_config',
                    'oro_entity_config_field',
                    'oro_entity_config_value',
                )
            )
        );
        $this->em->getConnection()->getDriver()->setSchemaManager($schema);

        $this->assertTrue($this->configModelManager->checkDatabase());
    }

    public function testFindModelIgnore()
    {
        $this->assertFalse(
            $this->configModelManager->findModel('Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel')
        );
    }

    public function testFindModelEntity()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new FoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $this->assertEquals(
            FoundEntityConfigRepository::getResultConfigEntity(),
            $configModelManager->findModel(DemoEntity::ENTITY_NAME)
        );

        //test localCache
        $this->assertEquals(
            FoundEntityConfigRepository::getResultConfigEntity(),
            $configModelManager->findModel(DemoEntity::ENTITY_NAME)
        );
    }

    public function testFindModelField()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new FoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $this->assertEquals(
            FoundEntityConfigRepository::getResultConfigField(),
            $configModelManager->findModel(DemoEntity::ENTITY_NAME, 'test')
        );
    }

    /**
     * @expectedException \Oro\Bundle\EntityConfigBundle\Exception\RuntimeException
     * @expectedExceptionMessage EntityConfigModel "Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\DemoEntity"
     * is not found
     */
    public function testGetModelEntityException()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);
        $em   = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new NotFoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $configModelManager->getModel(DemoEntity::ENTITY_NAME);
    }

    /**
     * @expectedException \Oro\Bundle\EntityConfigBundle\Exception\RuntimeException
     * @expectedExceptionMessage FieldConfigModel "Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\DemoEntity::test"
     * is not found
     */
    public function testGetModelFieldException()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);
        $em   = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new NotFoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $configModelManager->getModel(DemoEntity::ENTITY_NAME, 'test');
    }

    public function testGetModel()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);
        $em   = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new FoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $this->assertEquals(
            FoundEntityConfigRepository::getResultConfigEntity(),
            $configModelManager->getModel(DemoEntity::ENTITY_NAME)
        );
    }

    public function testGetModelByConfigId()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);
        $em   = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new FoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $this->assertEquals(
            FoundEntityConfigRepository::getResultConfigEntity(),
            $configModelManager->getModelByConfigId(new EntityConfigId(DemoEntity::ENTITY_NAME, 'test'))
        );
    }

    public function testGetModels()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);
        $em   = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new FoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $this->assertEquals(
            array(FoundEntityConfigRepository::getResultConfigEntity()),
            $configModelManager->getModels()
        );

        $this->assertEquals(
            array(FoundEntityConfigRepository::getResultConfigField()),
            $configModelManager->getModels(DemoEntity::ENTITY_NAME)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage EntityConfigModel give invalid parameter "mode" : "wrongMode"
     */
    public function testCreateEntityModelException()
    {
        $this->configModelManager->createEntityModel(DemoEntity::ENTITY_NAME, 'wrongMode');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage FieldConfigModel give invalid parameter "mode" : "wrongMode"
     */
    public function testCreateFieldModelException()
    {
        $this->configModelManager->createFieldModel(
            DemoEntity::ENTITY_NAME,
            'test',
            'string',
            'wrongMode'
        );
    }

    public function testCreateEntityModel()
    {
        $result = new EntityConfigModel(DemoEntity::ENTITY_NAME);
        $result->setMode(ConfigModelManager::MODE_DEFAULT);

        $this->assertEquals($result, $this->configModelManager->createEntityModel(DemoEntity::ENTITY_NAME));
    }

    public function testCreateFieldModel()
    {
        $meta = $this->em->getClassMetadata(EntityConfigModel::ENTITY_NAME);
        $em   = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getRepository')
            ->will($this->returnValue(new FoundEntityConfigRepository($em, $meta)));

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($em));

        $configModelManager = new ConfigModelManager($serviceLink);

        $entityModel = FoundEntityConfigRepository::getResultConfigEntity();

        $result = new FieldConfigModel('test', 'string');
        $result->setMode(ConfigModelManager::MODE_DEFAULT);

        $entityModel->addField($result);

        $this->assertEquals(
            $result,
            $configModelManager->createFieldModel(
                DemoEntity::ENTITY_NAME,
                'test',
                'string',
                ConfigModelManager::MODE_DEFAULT
            )
        );
    }
}
