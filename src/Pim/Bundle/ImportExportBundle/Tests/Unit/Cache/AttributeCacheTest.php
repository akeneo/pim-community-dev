<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Cache;

use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;

/**
 * Description of AttributeCacheTest
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class AttributeCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $attributeCache;
    protected $doctrine;
    protected $repository;

    protected $attributes;

    protected function setUp()
    {
        $this->attributes = array();
        $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->doctrine->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('PimCatalogBundle:ProductAttribute'))
            ->will($this->returnValue($this->repository));
        $this->attributeCache = new AttributeCache($this->doctrine);
    }
    protected function initializeAttributes()
    {
        $this->repository->expects($this->once())
            ->method('findBy')
            ->will($this->returnCallback(array($this, 'getAttributes')));
        $this->addAttribute('identifier', false, false, AttributeCache::IDENTIFIER_ATTRIBUTE_TYPE);
    }
    public function getAttributes($params)
    {
        $this->assertEquals(array_keys($this->attributes), array_values($params['code']));
        return $this->attributes;
    }
    protected function addAttribute($code, $translatable = false, $scopable = false, $attributeType = 'default')
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeType));

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));

        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue($scopable));

        $this->attributes[$code] = $attribute;
    }

    public function testInitialize()
    {
        $this->initializeAttributes();
        $this->addAttribute('col1');
        $this->addAttribute('col2', true);
        $this->addAttribute('col3', true, true);
        $this->addAttribute('col4', false, true);

        $this->attributeCache->initialize(
            array(
                'identifier',
                'col1',
                'col2-locale1',
                'col2-locale2',
                'col3-locale-scope',
                'col4-scope'
            )
        );

        $this->assertEquals($this->attributes, $this->attributeCache->getAttributes());
        $this->assertEquals($this->attributes['identifier'], $this->attributeCache->getIdentifierAttribute());
        $this->assertEquals($this->attributes['col1'], $this->attributeCache->getAttribute('col1'));
        $this->assertEquals(
            array(
                'identifier' => array(
                    'code'      => 'identifier',
                    'locale'    => null,
                    'scope'     => null,
                    'attribute' => $this->attributes['identifier']
                ),
                'col1' => array(
                    'code'      => 'col1',
                    'locale'    => null,
                    'scope'     => null,
                    'attribute' => $this->attributes['col1']
                ),
                'col2-locale1' => array(
                    'code'      => 'col2',
                    'locale'    => 'locale1',
                    'scope'     => null,
                    'attribute' => $this->attributes['col2']
                ),
                'col2-locale2' => array(
                    'code'      => 'col2',
                    'locale'    => 'locale2',
                    'scope'     => null,
                    'attribute' => $this->attributes['col2']
                ),
                'col3-locale-scope' => array(
                    'code'      => 'col3',
                    'locale'    => 'locale',
                    'scope'     => 'scope',
                    'attribute' => $this->attributes['col3']
                ),
                'col4-scope' => array(
                    'code'      => 'col4',
                    'locale'    => null,
                    'scope'     => 'scope',
                    'attribute' => $this->attributes['col4']
                )
            ),
            $this->attributeCache->getColumns()
        );
    }
}
