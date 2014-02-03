<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Validator\Import;

use Pim\Bundle\BaseConnectorBundle\Validator\Import\SkipImportValidator;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipImportValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validator = new SkipImportValidator();
        $this->assertEquals(array(), $validator->validate(new \stdClass, array(), array()));
    }
}
