<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Model;

use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AvailableProductAttributesTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $target = $this->getTargetedClass();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $target->getAttributes());
    }

    public function testGetSetAttributes()
    {
        $target = $this->getTargetedClass();
        $attributes = new ArrayCollection(array('foo', 'bar'));
        $target->setAttributes($attributes);

        $this->assertEquals($attributes, $target->getAttributes());
    }

    private function getTargetedClass()
    {
        return new AvailableProductAttributes();
    }
}
