<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Query;

use Oro\Bundle\SearchBundle\Query\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    private $config = array(
        'Oro\Bundle\DataBundle\Entity\Product' => array(
            'alias' => 'test_alias',
            'fields' => array(
                array(
                    'name' => 'name',
                    'target_type' => 'string',
                    'target_fields' => array('name', 'all_data')
                ),
                array(
                    'name' => 'description',
                    'target_type' => 'string',
                    'target_fields' => array('description', 'all_data')
                ),
                array(
                    'name' => 'price',
                    'target_type' => 'decimal',
                    'target_fields' => array('price')
                ),
                array(
                    'name' => 'count',
                    'target_type' => 'integer',
                    'target_fields' => array('count')
                ),
                array(
                    'name' => 'createDate',
                    'target_type' => 'datetime',
                    'target_fields' => array('create_date')
                ),
                array(
                    'name' => 'manufacturer',
                    'relation_type' => 'to',
                    'relation_fields' => array(
                        array(
                            'name' => 'name',
                            'target_type' => 'string',
                            'target_fields' => array('manufacturer', 'all_data')
                        )
                    )
                ),
            )
        )
    );

    /**
     * Set mapping config parameters
     */
    public function testSetMappingConfig()
    {
        $query = new Query();
        $query->setMappingConfig($this->config);

        $this->assertArrayHasKey('Oro\Bundle\DataBundle\Entity\Product', $query->getMappingConfig());
        $this->assertArrayHasKey('all_data', $query->getFields());
    }

    public function testAndWhere()
    {
        $query = new Query();
        $query->setMappingConfig($this->config);
        $query->from('Oro\Bundle\DataBundle\Entity\Product');
        $query->andWhere('all_data', '=', 'test', 'string');
        $queryParams = $query->getOptions();
        $this->assertEquals('and', $queryParams[0]['type']);
        $this->assertEquals('all_data', $queryParams[0]['fieldName']);
    }

    public function testGetMaxResults()
    {
        $query = new Query();
        $query->setMaxResults(10);
        $this->assertEquals(10, $query->getMaxResults());
    }

    public function testOrWhere()
    {
        $query = new Query();
        $query->setMappingConfig($this->config);
        $query->from('Oro\Bundle\DataBundle\Entity\Product');
        $query->orWhere('all_data', '=', 'test', 'string');
        $queryParams = $query->getOptions();
        $this->assertEquals('or', $queryParams[0]['type']);
        $this->assertEquals('all_data', $queryParams[0]['fieldName']);
    }

    public function testWhere()
    {
        $query = new Query();
        $query->setMappingConfig($this->config);
        $query->from('Oro\Bundle\DataBundle\Entity\Product');
        $query->where('or', 'all_data', '=', 'test', 'string');
        $queryParams = $query->getOptions();
        $this->assertEquals('or', $queryParams[0]['type']);
        $this->assertEquals('all_data', $queryParams[0]['fieldName']);
    }

    public function testGetEntityByAlias()
    {
        $query = new Query();
        $query->setMappingConfig($this->config);
        $result = $query->getEntityByAlias('test_alias');
        $this->assertEquals('Oro\Bundle\DataBundle\Entity\Product', $result);

        $this->assertFalse($query->getEntityByAlias('bad alias'));
    }
}
