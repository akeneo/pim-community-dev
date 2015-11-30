<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Cache;

use Pim\Bundle\TransformBundle\Cache\AttributeCache;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $attributeCache;
    protected $doctrine;
    protected $repository;

    protected $attributes;
    protected $expectedQueryCodes;
    protected $families;
    protected $groups;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->attributes = [];
        $this->expectedQueryCodes = null;
        $this->families = [];
        $this->groups = [];
        $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->doctrine->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('attribute_class'))
            ->will($this->returnValue($this->repository));
        $this->attributeCache = new AttributeCache($this->doctrine, 'attribute_class');
    }

    /**
     * Test related method
     */
    protected function initializeAttributes()
    {
        $this->repository->expects($this->once())
            ->method('findBy')
            ->will($this->returnCallback([$this, 'getAttributes']));
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getAttributes($params)
    {
        $this->assertEquals($this->expectedQueryCodes ?: array_keys($this->attributes), array_values($params['code']));

        return $this->attributes;
    }

    /**
     * @param string $code
     * @param string $attributeType
     *
     * @return \Pim\Component\Catalog\Model\AttributeInterface
     */
    public function addAttribute($code, $attributeType = 'default')
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeType));

        $this->attributes[$code] = $attribute;

        return $this->attributes[$code];
    }

    /**
     * Test related method
     */
    public function testGetAttributes()
    {
        $this->initializeAttributes();
        $this->addAttribute('col1');
        $this->addAttribute('col2');

        $attributes = $this->attributeCache->getAttributes(
            [
                $this->getColumnInfoMock('col1'),
                $this->getColumnInfoMock('col2'),
            ]
        );

        $this->assertEquals($this->attributes, $attributes);
        $this->assertEquals($this->attributes['col1'], $attributes['col1']);
    }

    public function testGetEmptyAttributes()
    {
        $attributes = $this->attributeCache->getAttributes([]);
        $this->assertEquals([], $attributes);
    }

    /**
     * Test related method
     */
    public function testGetRequiredAttributes()
    {
        $product1 = $this->getProductMock(
            null,
            [],
            'family1',
            ['key1', 'key2'],
            ['group1' => ['key3', 'key4', 'key5'], 'group2' => ['key7']]
        );
        $this->assertEqualArrays(
            ['key1', 'key2', 'key3', 'key4', 'key5', 'key7'],
            $this->attributeCache->getRequiredAttributeCodes($product1)
        );

        $product2 = $this->getProductMock(
            1,
            ['key0'],
            'family2',
            ['key8'],
            ['group1' => [], 'group3' => ['key3', 'key9']]
        );
        $this->assertEqualArrays(
            ['key0', 'key3', 'key4', 'key5', 'key8', 'key9'],
            $this->attributeCache->getRequiredAttributeCodes($product2)
        );

        $product3 = $this->getProductMock(
            null,
            [],
            'family1'
        );
        $this->assertEqualArrays(
            ['key1', 'key2'],
            $this->attributeCache->getRequiredAttributeCodes($product3)
        );

        $product4 = $this->getProductMock();
        $this->assertEqualArrays(
            [],
            $this->attributeCache->getRequiredAttributeCodes($product4)
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     *
     * @return bool
     */
    protected function assertEqualArrays($expected, $actual)
    {
        sort($expected);
        sort($actual);

        return $this->assertEquals($expected, $actual);
    }

    /**
     * @param int    $productId
     * @param array  $attributeCodes
     * @param string $familyCode
     * @param array  $familyAttributeCodes
     * @param array  $categories
     *
     * @return \Pim\Component\Catalog\Model\ProductInterface
     */
    protected function getProductMock(
        $productId = null,
        $attributeCodes = [],
        $familyCode = null,
        array $familyAttributeCodes = [],
        array $categories = []
    ) {
        $product = $this->getMockBuilder('Pim\Component\Catalog\Model\Product')
            ->setMethods(['getId', 'getValues', 'getFamily', 'getGroups'])
            ->getMock();
        $product->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($productId));
        $values = [];
        foreach ($attributeCodes as $attributeCode) {
            $value = $this->getMockBuilder('Pim\Component\Catalog\Model\ProductValue')
                ->setMethods(['getAttribute', '__toString'])
                ->getMock();
            $value->expects($this->any())
                ->method('getAttribute')
                ->will($this->returnValue($this->addAttribute($attributeCode)));
            $values[] = $value;
        }
        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

        if (null !== $familyCode) {
            if (!isset($this->families[$familyCode])) {
                $this->families[$familyCode] = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');
                $this->addAttributeCollection($this->families[$familyCode], $familyCode, $familyAttributeCodes);
            }
            $product
                ->expects($this->any())
                ->method('getFamily')
                ->will($this->returnValue($this->families[$familyCode]));
        }

        $groups = [];
        foreach ($categories as $groupCode => $groupAttributeCodes) {
            if (!isset($this->groups[$groupCode])) {
                $this->groups[$groupCode] = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Group');
                $this->addAttributeCollection($this->groups[$groupCode], $groupCode, $groupAttributeCodes);
            }
            $groups[] = $this->groups[$groupCode];
        }
        $product
            ->expects($this->any())
            ->method('getGroups')
            ->will($this->returnValue($groups));

        return $product;
    }

    /**
     * @param object $entity
     * @param string $code
     * @param array  $attributeCodes
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function addAttributeCollection($entity, $code, array $attributeCodes)
    {
        $test = $this;
        $collection = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        $collection->expects($this->once())
            ->method('toArray')
            ->will(
                $this->returnValue(
                    array_map(
                        function ($code) use ($test) {
                            return $test->addAttribute($code);
                        },
                        $attributeCodes
                    )
                )
            );
        $entity->expects($this->any())
                ->method('getCode')
                ->will($this->returnValue($code));
        $entity->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($collection));

        return $collection;
    }

    protected function getColumnInfoMock($name, $withAttribute = true)
    {
        $info = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $info->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $info;
    }
}
