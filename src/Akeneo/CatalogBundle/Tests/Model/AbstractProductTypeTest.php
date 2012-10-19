<?php
namespace Akeneo\CatalogBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;
use Akeneo\CatalogBundle\Tests\Model\KernelAwareTest;

/**
 * Provide abstract test for product type model (can be used for different implementation)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbtractProductTypeTest extends KernelAwareTest
{
    const TYPE_BASE          = 'base_test';
    const TYPE_GROUP_INFO    = 'general';
    const TYPE_GROUP_MEDIA   = 'media';
    const TYPE_GROUP_SEO     = 'seo';
    const TYPE_GROUP_TECHNIC = 'technical';

    protected $serviceName = null;
    protected $modelType   = null;
    protected $modelEntity = null;
    protected $entity      = null;
    protected $entityType  = null;
    protected $entityGroup = null;
    protected $entityField = null;

    protected $newTypeCode = null;

    /**
    * (non-documented)
    * TODO : Automatic link to PHPUnit_Framework_TestCase::setUp documentation
    */
    public function setUp()
    {
        parent::setUp();
        if (!$this->newTypeCode) {
            $this->newTypeCode = self::TYPE_BASE.'_'.time();
            $manager = $this->container->get($this->serviceName);
            $manager->create($this->newTypeCode);
        }
    }

    /**
     * test related method
     */
    public function testFind()
    {
        $manager = $this->container->get($this->serviceName);
        $manager->find($this->newTypeCode);
        $this->assertInstanceOf($this->modelType, $manager);
        $this->assertInstanceOf($this->entityType, $manager->getObject());
        $this->assertEquals($manager->getCode(), $this->newTypeCode);
    }

    /**
     * test related method
     */
    public function testCreate()
    {
        $code = $this->newTypeCode.'_2';
        $manager = $this->container->get($this->serviceName);
        $manager->create($code);
        $this->assertInstanceOf($this->modelType, $manager);
        $this->assertInstanceOf($this->entityType, $manager->getObject());
    }

    /**
    * test basic getters / setters
    */
    public function testGettersSetters()
    {
        $code = $this->newTypeCode.'_3';
        $manager = $this->container->get($this->serviceName);
        $manager->create($code);
        $this->assertEquals($code, $manager->getCode());
        $title = 'my title';
        $manager->setTitle($title);
        $this->assertEquals($title, $manager->getTitle());
    }

    /**
     * test method related to groups and fields
     */
    public function testGroupsAndFields()
    {
        $manager = $this->container->get($this->serviceName);
        $manager->find($this->newTypeCode);

        // add info fields
        $fields = array('sku', 'name', 'short_description', 'description', 'color');
        foreach ($fields as $fieldCode) {
            $manager->addField($fieldCode, 'text', self::TYPE_GROUP_INFO);
        }

        // add media fields
        $fields = array('image', 'thumbnail');
        foreach ($fields as $fieldCode) {
            $manager->addField($fieldCode, 'text', self::TYPE_GROUP_MEDIA);
        }

        // add others empty groups
        $manager->addGroup(self::TYPE_GROUP_SEO);
        $manager->addGroup(self::TYPE_GROUP_TECHNIC);

        $this->assertEquals(count($manager->getGroupsCodes()), 4);

        // TODO : nb fields


        // persist type
        $manager->persist();
        $manager->flush();

        // create product and related service
        $productManager = $manager->newProductInstance();
        $this->assertInstanceOf($this->entity, $productManager->getObject());

        // remove
        $manager->remove();

        // TODO translate behaviour
    }

}