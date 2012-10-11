<?php
namespace Akeneo\CatalogBundle\Tests\Entity;

use \PHPUnit_Framework_TestCase;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Default entity tested
     * @var Entity
     */
    protected $entity;

    /**
     * Redefine constructor to call initialize method
     * @param string $name
     * @param array $data
     */
    public function __construct($name = NULL, array $data = array())
    {
        parent::__construct($name, $data);
        $this->initialize();
    }

    /**
     * Initialization method for the test
     */
    protected function initialize()
    {
    }

    /**
     * Return the entity class name tested
     * @abstract
     * @return string
     */
    abstract protected function getEntityClassName();

    /**
     * (non-documented)
     * TODO : Automatic link to PHPUnit_Framework_TestCase::setUp documentation
     */
    public function setUp()
    {
        parent::setUp();
        $this->entity = $this->createEntity();
    }

    /**
     * (non-documented)
     * TODO : Automatic link to PHPUnit_Framework_TestCase::tearDown documentation
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Create the entity tested
     * @return Entity
     */
    protected function createEntity()
    {
        $className = $this->getEntityClassName();
        return new $className();
    }

    /**
     * Assert an entity class
     * @param string $className
     * @param Entity $obj
     */
    protected function assertClass($className, $obj)
    {
        $this->assertInstanceOf($className, $obj);

        // TODO assert attributes
    }

    /**
     * Test constructor for each entity
     */
    public function testConstructor()
    {
        $obj = $this->createEntity();
        $this->assertClass($this->getEntityClassName(), $obj);
    }
}