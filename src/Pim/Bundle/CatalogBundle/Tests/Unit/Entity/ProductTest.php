<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Completeness;
use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->product = new Product();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Product', $this->product);

        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->product->getCategories());
        $this->assertCount(0, $this->product->getCategories());

        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->product->getCompletenesses());
        $this->assertCount(0, $this->product->getCompletenesses());
    }

    /**
     * Test getter/setter for family property
     */
    public function testGetSetFamily()
    {
        $this->assertEmpty($this->product->getFamily());

        // Change value and assert new
        $newFamily = new Family();
        $this->product->setFamily($newFamily);
        $this->assertEquals($newFamily, $this->product->getFamily());

        $this->product->setFamily(null);
        $this->assertNull($this->product->getFamily());
    }

    /**
     * Test related method
     */
    public function testGetAttributes()
    {
        $attributes = array(
            $this->getAttributeMock(),
            $this->getAttributeMock(),
            $this->getAttributeMock(),
        );

        foreach ($attributes as $attribute) {
            $this->product->addValue($this->getValueMock($attribute));
        }

        $this->assertEquals($attributes, $this->product->getAttributes());
    }

    /**
     * Test related method
     */
    public function testGetGroups()
    {
        $groups           = array(
            $otherGroup   = $this->getGroupMock(1, 'Other', -1),
            $generalGroup = $this->getGroupMock(2, 'General', 0),
            $alphaGroup   = $this->getGroupMock(3, 'Alpha', 20),
            $alphaGroup2  = $this->getGroupMock(3, 'Alpha', 20),
            $betaGroup    = $this->getGroupMock(4, 'Beta', 10),
        );

        foreach ($groups as $group) {
            $this->product->addValue($this->getValueMock($this->getAttributeMock($group)));
        }

        $groups = $this->product->getOrderedGroups();
        $this->assertSame(4, count($groups));
        $this->assertSame($generalGroup, current($groups));
        $this->assertSame($betaGroup, next($groups));
        $this->assertSame($alphaGroup2, next($groups));
        $this->assertSame($otherGroup, next($groups));
    }

    public function testSkuLabel()
    {
        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    public function testAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();

        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, 'bar');

        $this->product->setId(10);
        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('bar', $this->product->getLabel());
    }

    public function testNullValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, null);

        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    public function testEmptyStringValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, '');

        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    public function testNullAttributeLabel()
    {
        $attribute = $this->getAttributeMock();
        $family    = $this->getFamilyMock(null);
        $value     = $this->getValueMock($attribute, 'bar');

        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    public function testIsSetEnabled()
    {
        $this->assertTrue($this->product->isEnabled());

        $this->product->setEnabled(false);
        $this->assertFalse($this->product->isEnabled());

        $this->product->setEnabled(true);
        $this->assertTrue($this->product->isEnabled());
    }

    public function testGetIdentifier()
    {
        $identifier = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'));
        $name       = $this->getValueMock($this->getAttributeMock());

        $this->product->addValue($identifier);
        $this->product->addValue($name);

        $this->assertSame($identifier, $this->product->getIdentifier());
    }

    /**
     * @expectedException Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException
     */
    public function testThrowExceptionIfNoIdentifier()
    {
        $name    = $this->getValueMock($this->getAttributeMock());

        $this->product->addValue($name);

        $this->product->getIdentifier();
    }

    /**
     * Test completenesses property and method linked
     */
    public function testCompletenesses()
    {
        // create 2 completeness entities
        $completeness = $this->createCompleteness('channel1', 'en_US');
        $localeUS = $completeness->getLocale();
        $channel1 = $completeness->getChannel();

        $completeness2 = $this->createCompleteness('channel2', 'fr_FR');
        $localeFR = $completeness2->getLocale();
        $channel2 = $completeness2->getChannel();

        // assert no return if nothing found
        $this->assertNull($this->product->getCompleteness($localeUS, $channel1));

        // assert add new completeness
        $this->assertEntity($this->product->addCompleteness($completeness));
        $this->assertCount(1, $this->product->getCompletenesses());
        $this->assertEquals($completeness, $this->product->getCompleteness($localeUS->getCode(), $channel1->getCode()));

        // assert no duplicate adding
        $this->assertEntity($this->product->addCompleteness($completeness));
        $this->assertCount(1, $this->product->getCompletenesses());

        // assert remove adding a second completeness
        $this->product->addCompleteness($completeness2);
        $this->assertCount(2, $this->product->getCompletenesses());
        $this->assertEntity($this->product->removeCompleteness($completeness));
        $this->assertCount(1, $this->product->getCompletenesses());
        $this->assertNull($this->product->getCompleteness($localeUS->getCode(), $channel1->getCode()));

        // assert remove an already remove completeness
        $this->assertEntity($this->product->removeCompleteness($completeness));
        $this->assertCount(1, $this->product->getCompletenesses());

        // assert setter completenesses
        $this->assertEntity($this->product->setCompletenesses());
        $this->assertCount(0, $this->product->getCompletenesses());

        $this->product->setCompletenesses(array($completeness, $completeness2));
        $this->assertCount(2, $this->product->getCompletenesses());
    }

    /**
     * Create completeness entity
     *
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Completeness
     */
    protected function createCompleteness($channelCode, $localeCode)
    {
        $completeness = new Completeness();
        $completeness->setChannel($this->createChannel($channelCode));
        $completeness->setLocale($this->createLocale($localeCode));

        return $completeness;
    }

    /**
     * Create channel entity
     *
     * @param string $channelCode
     *
     * @return \Pim\Bundle\CatalogBundle\Tests\Unit\Entity\Channel
     */
    protected function createChannel($channelCode)
    {
        $channel = new Channel();
        $channel->setCode($channelCode);

        return $channel;
    }

    /**
     * Create locale entity
     *
     * @param string $localeCode
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    protected function createLocale($localeCode)
    {
        $locale = new Locale();
        $locale->setCode($localeCode);

        return $locale;
    }

    private function getAttributeMock($group = null, $type = 'pim_catalog_text')
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getVirtualGroup')
            ->will($this->returnValue($group));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($type));

        return $attribute;
    }

    private function getValueMock($attribute, $data = null)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductValue');

        $value->expects($this->any())
              ->method('getAttribute')
              ->will($this->returnValue($attribute));

        $value->expects($this->any())
              ->method('getData')
              ->will($this->returnValue($data));

        $value->expects($this->any())
            ->method('isMatching')
            ->will($this->returnValue(true));

        return $value;
    }

    private function getGroupMock($id, $name, $sortOrder)
    {
        $group = $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeGroup');

        $group->expects($this->any())
              ->method('getId')
              ->will($this->returnValue($id));

        $group->expects($this->any())
              ->method('getSortOrder')
              ->will($this->returnValue($sortOrder));

        $group->expects($this->any())
              ->method('getName')
              ->will($this->returnValue($name));

        return $group;
    }

    private function getFamilyMock($attributeAsLabel)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute', array('getAttributeAsLabel'));

        $attribute->expects($this->any())
                  ->method('getAttributeAsLabel')
                  ->will($this->returnValue($attributeAsLabel));

        return $attribute;
    }

    /**
     * Assert tested entity
     *
     * @param Product $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Product', $entity);
    }
}
