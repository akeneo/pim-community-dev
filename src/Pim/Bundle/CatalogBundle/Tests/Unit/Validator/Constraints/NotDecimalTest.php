<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\NotDecimal;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testExtendsConstraint()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraint', new NotDecimal);
    }
}
