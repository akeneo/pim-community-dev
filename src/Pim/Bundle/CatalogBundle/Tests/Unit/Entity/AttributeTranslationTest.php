<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTranslationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testConstruct()
    {
        $attribute = new AttributeTranslation();

        $this->assertInstanceOf('\Pim\Bundle\TranslationBundle\Entity\AbstractTranslation', $attribute);
    }
}
