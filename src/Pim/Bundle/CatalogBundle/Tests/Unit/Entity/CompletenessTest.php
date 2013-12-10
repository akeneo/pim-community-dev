<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\Completeness;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\Completeness $completeness
     */
    protected $completeness;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->completeness = new Completeness();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->completeness);

        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(0, $this->completeness->getMissingAttributes());
    }

    /**
     * Assert completeness entity
     *
     * @param Completeness $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Completeness', $entity);
    }

    /**
     * Test getter/setter for locale property
     */
    public function testGetSetLocale()
    {
        $this->assertNull($this->completeness->getLocale());

        $expectedLocale = $this->createLocale('en_US');

        $this->assertEntity($this->completeness->setLocale($expectedLocale));
        $this->assertEquals($expectedLocale, $this->completeness->getLocale());
    }

    /**
     * Create a locale
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    protected function createLocale($code)
    {
        $locale = new Locale();
        $locale->setCode($code);

        return $locale;
    }

    /**
     * Test getter/setter for channel property
     */
    public function testGetSetChannel()
    {
        $this->assertNull($this->completeness->getChannel());

        $expectedChannel = $this->createChannel('channel');

        $this->assertEntity($this->completeness->setChannel($expectedChannel));
        $this->assertEquals($expectedChannel, $this->completeness->getChannel());
    }

    /**
     * Create a channel
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function createChannel($code)
    {
        $channel = new Channel();
        $channel->setCode($code);

        return $channel;
    }

    /**
     * Test getter/setter for ratio property
     */
    public function testGetSetRatio()
    {
        $this->assertEquals(100, $this->completeness->getRatio());

        $expectedRatio = 53.83;
        $this->assertEntity($this->completeness->setRatio($expectedRatio));
        $this->assertEquals($expectedRatio, $this->completeness->getRatio());

        $expectedRatio = 35;
        $this->completeness->setRatio($expectedRatio);
        $this->assertEquals($expectedRatio, $this->completeness->getRatio());
    }

    /**
     * Test getter/setter for missing count property
     */
    public function testGetSetMissingCount()
    {
        $this->assertEquals(0, $this->completeness->getMissingCount());

        $expectedMissingCount = 4;
        $this->assertEntity($this->completeness->setMissingCount($expectedMissingCount));
        $this->assertEquals($expectedMissingCount, $this->completeness->getMissingCount());
    }

    /**
     * Test getter/setter for required count property
     */
    public function testGetSetRequiredCount()
    {
        $this->assertEquals(0, $this->completeness->getRequiredCount());

        $expectedRequiredCount = 3;
        $this->assertEntity($this->completeness->setRequiredCount($expectedRequiredCount));
        $this->assertEquals($expectedRequiredCount, $this->completeness->getRequiredCount());
    }

    /**
     * Test getter/setter for product property
     */
    public function testGetSetProduct()
    {
        $this->assertNull($this->completeness->getProduct());

        $expectedProduct = new Product();
        $this->assertEntity($this->completeness->setProduct($expectedProduct));
        $this->assertEquals($expectedProduct, $this->completeness->getProduct());
    }

    /**
     * Test getter/setter for missing attributes
     */
    public function testGetSetMissingAttributes()
    {
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(0, $this->completeness->getMissingAttributes());

        // assert with 1 attribute
        $expectedAttribute = $this->createAttribute('test');
        $expectedList = array($expectedAttribute);

        $this->assertEntity($this->completeness->setMissingAttributes($expectedList));
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(1, $this->completeness->getMissingAttributes());

        // assert setter can reinitialize array
        $this->completeness->setMissingAttributes();
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(0, $this->completeness->getMissingAttributes());

        // assert with 2 attributes
        $expectedAttribute2 = $this->createAttribute('test2');
        $expectedList = array($expectedAttribute, $expectedAttribute2);

        $this->completeness->setMissingAttributes($expectedList);
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(2, $this->completeness->getMissingAttributes());
    }

    /**
     * Test add/remove methods for missing attributes
     */
    public function testAddRemoveMissingAttributes()
    {
        // assert adding an attribute
        $expectedAttribute = $this->createAttribute('test');
        $this->assertEntity($this->completeness->addMissingAttribute($expectedAttribute));
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(1, $this->completeness->getMissingAttributes());

        // assert adding the same attribute
        $this->assertEntity($this->completeness->addMissingAttribute($expectedAttribute));
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(1, $this->completeness->getMissingAttributes());

        // assert adding a second attribute
        $expectedAttribute2 = $this->createAttribute('test2');
        $this->completeness->addMissingAttribute($expectedAttribute2);
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(2, $this->completeness->getMissingAttributes());

        // assert removing the first attribute
        $this->assertEntity($this->completeness->removeMissingAttribute($expectedAttribute));
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(1, $this->completeness->getMissingAttributes());
        $remainingAttribute = $this->completeness->getMissingAttributes()->first();
        $this->assertEquals($expectedAttribute2, $remainingAttribute);

        // assert removing the same attribute
        $this->assertEntity($this->completeness->removeMissingAttribute($expectedAttribute));
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->completeness->getMissingAttributes());
        $this->assertCount(1, $this->completeness->getMissingAttributes());
    }

    /**
     * Create product attribute
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    protected function createAttribute($code)
    {
        $attribute = new ProductAttribute();
        $attribute->setCode($code);

        return $attribute;
    }
}
