<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\PendingCompleteness;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\Channel;
use Pim\Bundle\ProductBundle\Entity\Locale;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingCompletenessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\ProductBundle\Entity\PendingCompleteness $pending
     */
    protected $pending;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->pending = new PendingCompleteness();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->pending);
    }

    /**
     * Test getter/setter for locale property
     */
    public function testGetSetLocale()
    {
        $this->assertNull($this->pending->getLocale());
        $expected = new Locale();
        $this->assertEntity($this->pending->setLocale($expected));
        $this->assertEquals($expected, $this->pending->getLocale());
    }

    /**
     * Test getter/setter for channel property
     */
    public function testGetSetChannel()
    {
        $this->assertNull($this->pending->getChannel());
        $expected = new Channel();
        $this->assertEntity($this->pending->setChannel($expected));
        $this->assertEquals($expected, $this->pending->getChannel());
    }

    /**
     * Test getter/setter for family property
     */
    public function testGetSetFamily()
    {
        $this->assertNull($this->pending->getFamily());
        $expected = new Family();
        $this->assertEntity($this->pending->setFamily($expected));
        $this->assertEquals($expected, $this->pending->getFamily());
    }

    /**
     * Assert completeness entity
     *
     * @param Completeness $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\PendingCompleteness', $entity);
    }
}
