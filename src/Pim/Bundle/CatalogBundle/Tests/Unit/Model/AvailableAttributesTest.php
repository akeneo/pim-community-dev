<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Model;

use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableAttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testConstructor()
    {
        $target = $this->getTargetedClass();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $target->getAttributes());
    }

    /**
     * Test related method
     */
    public function testGetSetAttributes()
    {
        $target = $this->getTargetedClass();
        $attributes = new ArrayCollection(['foo', 'bar']);
        $target->setAttributes($attributes);

        $this->assertEquals($attributes, $target->getAttributes());
    }

    /**
     * @return AvailableAttributes
     */
    private function getTargetedClass()
    {
        return new AvailableAttributes();
    }
}
