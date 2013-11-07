<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Media;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->media = new Media();
    }

    public function testSetGetValue()
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValueInterface');
        $this->media->setValue($value);

        $this->assertEquals($value, $this->media->getValue());
    }
}
