<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Exception;

use Pim\Bundle\ImportExportBundle\Exception\DuplicateIdentifierException;

/**
 * Tests related class
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateIdentifierExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $ex = new DuplicateIdentifierException('id', array('key1' => 'val1'));
        $this->assertEquals('The unique code "id" was already read in this file', $ex->getMessage());
        $this->assertEquals('The unique code "%identifier%" was already read in this file', $ex->getMessageTemplate());
        $this->assertEquals(array('%identifier%' => 'id'), $ex->getMessageParameters());
        $this->assertEquals(array('key1' => 'val1'), $ex->getItem());
    }
}
