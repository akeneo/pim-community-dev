<?php
namespace Akeneo\CatalogBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;
use Akeneo\CatalogBundle\Tests\Model\KernelAwareTest;

/**
 *
 * Aims to test product model
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTest extends KernelAwareTest
{

    /**
     * Avoid empty test
     */
    public function testOne()
    {
        $this->assertEquals(1, 1);
    }
}