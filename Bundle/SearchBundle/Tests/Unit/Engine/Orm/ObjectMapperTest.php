<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Engine\Orm;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;

use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Manufacturer;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Attribute;

class ObjectMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Engine\ObjectMapper
     */
    private $mapper;
    private $mappingConfig
        = array(
            'Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Manufacturer' => array(
                'fields' => array(
                    array(
                        'name'            => 'products',
                        'relation_type'   => 'one-to-many',
                        'relation_fields' => array(
                            array(
                                'name'        => 'name',
                                'target_type' => 'text',
                            )
                        )
                    ),
                    array(
                        'name'            => 'parent',
                        'relation_type'   => 'one-to-many',
                        'relation_fields' => array(
                            array()
                        )
                    )
                )
            ),
            'Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product'      => array(
                'alias'            => 'test_product',
                'label'            => 'test product',
                'title_fields'     => array('name'),
                'route'            => array(
                    'name'       => 'test_route',
                    'parameters' => array(
                        'id' => 'id'
                    )
                ),
                'fields'           => array(
                    array(
                        'name'          => 'name',
                        'target_type'   => 'text',
                        'target_fields' => array(
                            'name',
                            'all_data'
                        )
                    ),
                    array(
                        'name'          => 'description',
                        'target_type'   => 'text',
                        'target_fields' => array(
                            'description',
                            'all_data'
                        )
                    ),
                    array(
                        'name'          => 'price',
                        'target_type'   => 'decimal',
                        'target_fields' => array('price')
                    ),
                    array(
                        'name'        => 'count',
                        'target_type' => 'integer',
                    ),
                    array(
                        'name'            => 'manufacturer',
                        'relation_type'   => 'one-to-one',
                        'relation_fields' => array(
                            array(
                                'name'          => 'name',
                                'target_type'   => 'text',
                                'target_fields' => array(
                                    'manufacturer',
                                    'all_data'
                                )
                            )
                        )
                    ),
                ),
            )
        );

    public function setUp()
    {
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');

        $manufacturer = new Manufacturer();
        $manufacturer->setName('adidas');

        $this->product = new Product();
        $this->product->setName('test product')
            ->setCount(10)
            ->setPrice(150)
            ->setManufacturer($manufacturer)
            ->setDescription('description')
            ->setCreateDate(new \DateTime());

        $this->route = $this
            ->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->route->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('http://example.com'));
        $params = array(
            'router'       => $this->route,
        );
        $this->container->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalOr(
                    $this->equalTo('test_manager'),
                    $this->equalTo('router')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($param) use (&$params) {
                        return $params[$param];
                    }
                )
            );

        $this->mapper = new ObjectMapper($this->container, $this->mappingConfig);
    }

    public function testMapObject()
    {
        $mapping = $this->mapper->mapObject($this->product);

        $this->assertEquals('test product ', $mapping['text']['name']);
        $this->assertEquals(150, $mapping['decimal']['price']);
        $this->assertEquals(10, $mapping['integer']['count']);

        $manufacturer = new Manufacturer();
        $manufacturer->setName('reebok');
        $manufacturer->addProduct($this->product);
        $this->mapper->mapObject($manufacturer);
    }

    public function testGetEntitiesListAliases()
    {
        $data = $this->mapper->getEntitiesListAliases();

        $this->assertEquals('test_product', $data['Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product']);
    }

    public function testGetMappingConfig()
    {
        $mapping = $this->mappingConfig;

        $this->assertEquals($mapping, $this->mapper->getMappingConfig());
    }

    public function testGetEntityMapParameter()
    {
        $this->assertEquals(
            'test_product',
            $this->mapper->getEntityMapParameter(
                'Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product',
                'alias'
            )
        );

        $this->assertEquals(
            false,
            $this->mapper->getEntityMapParameter(
                'Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product',
                'non exists parameter'
            )
        );
    }

    public function testGetEntities()
    {
        $entities = $this->mapper->getEntities();
        $this->assertEquals('Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product', $entities[1]);
    }

    public function testNonExistsConfig()
    {
        $this->assertEquals(false, $this->mapper->getEntityConfig('non exists entity'));
    }
}
