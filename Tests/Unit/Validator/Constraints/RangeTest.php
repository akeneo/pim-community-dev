<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ProductBundle\Validator\Constraints\Range;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeTest extends \PHPUnit_Framework_TestCase
{
    public function testExtendsRangeConstraint()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\Range', new Range(array('min' => 0)));
    }
}

