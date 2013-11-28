<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\ColumnInfo;

use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnInfoTransformerTest extends \PHPUnit_Framework_TestCase
{
    const COLUMN_INFO_CLASS = 'Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfo';

    public function testSingle()
    {
        $transformer = new ColumnInfoTransformer(static::COLUMN_INFO_CLASS);
        $result = $transformer->transform('class', 'label');
        $this->assertColumnInfo($result, 'label');
        $result2 = $transformer->transform('class', 'label');
        $this->assertSame($result, $result2);
        $result3 = $transformer->transform('class2', 'label');
        $this->assertNotSame($result, $result3);
    }
    public function testMultiple()
    {
        $transformer = new ColumnInfoTransformer(static::COLUMN_INFO_CLASS);
        $result = $transformer->transform('class', array('label1', 'label2'));
        $this->assertColumnInfo($result[0], 'label1');
        $this->assertColumnInfo($result[1], 'label2');
        $result2 = $transformer->transform('class', array('label1', 'label2'));
        $this->assertSame($result[0], $result2[0]);
        $this->assertSame($result[1], $result2[1]);
        $result3 = $transformer->transform('class2', array('label1', 'label2'));
        $this->assertNotSame($result[0], $result3[0]);
        $this->assertNotSame($result[1], $result3[1]);
    }
    protected function assertColumnInfo($columnInfo, $label)
    {
        $this->assertInstanceOf(static::COLUMN_INFO_CLASS, $columnInfo);
        $this->assertEquals($label, $columnInfo->getLabel());
    }
}
