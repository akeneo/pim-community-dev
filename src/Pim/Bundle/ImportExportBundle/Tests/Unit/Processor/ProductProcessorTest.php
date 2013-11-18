<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\ProductProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testProcess()
    {
        $productTransformer = $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Transformer\ORMProductTransformer')
            ->disableOriginalConstructor()
            ->getMock();

        $processor = new ProductProcessor($productTransformer);

        $processor->setEnabled('enabled');
        $processor->setFamilyColumn('fml');
        $processor->setCategoriesColumn('ctg');
        $processor->setGroupsColumn('grp');

        $data = array('key'=>'val');

        $productTransformer->expects($this->once())
            ->method('getProduct')
            ->with(
                $this->equalTo($data),
                $this->equalTo(
                    array(
                        'family'        => 'fml',
                        'categories'    => 'ctg',
                        'groups'        => 'grp'
                    )
                ),
                $this->equalTo(
                    array(
                        'enabled' => 'enabled'
                    )
                )
            )
            ->will($this->returnValue('success'));

        $this->assertEquals('success', $processor->process($data));
    }
}
