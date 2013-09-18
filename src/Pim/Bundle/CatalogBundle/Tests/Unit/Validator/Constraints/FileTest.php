<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\File;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testExtendsFileConstraint()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\File', new File());
    }
}
