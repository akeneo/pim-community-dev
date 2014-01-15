<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Action\Export;

/**
 * Abstract test case for export actions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ExportActionTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Creates export action class
     *
     * @param array $options
     *
     * @return Pim\Bundle\GridBundle\Action\Export\ExportActionInterface
     */
    abstract protected function createExportAction(array $options);

    /**
     * Data provider for export action constructor
     *
     * @return array
     */
    abstract public static function constructDataProvider();

    /**
     * Test constructor
     * @param array $expectedOptions
     * @param array $inputOptions
     *
     * @dataProvider constructDataProvider
     */
    public function testConstruct(array $expectedOptions, array $inputOptions)
    {
        $exportAction = $this->createExportAction($inputOptions);
        $this->assertEquals($expectedOptions, $exportAction->getOptions());
    }

    /**
     * Data provider for throwing of InvalidArgumentException
     *
     * @return array
     */
    abstract public static function invalidArgumentExceptionDataProvider();

    /**
     * Test the throwing of InvalidArgumentException
     * @param array $inputOptions
     *
     * @dataProvider invalidArgumentExceptionDataProvider
     * @expectedException InvalidArgumentException
     */
    public function testThrowsInvalidArgumentException(array $inputOptions)
    {
        $this->createExportAction($inputOptions);
    }

    /**
     * Data provider for getters
     *
     * @return array
     */
    abstract public static function dataProviderGetters();

    /**
     * Test related method
     * @param array $inputOptions
     *
     * @dataProvider dataProviderGetters
     */
    public function testGetName($inputOptions)
    {
        $exportAction = $this->createExportAction($inputOptions);
        $this->assertEquals($inputOptions['name'], $exportAction->getName());
    }

    /**
     * Test related method
     * @param array $inputOptions
     *
     * @dataProvider dataProviderGetters
     */
    public function testGetAclResource($inputOptions)
    {
        $exportAction = $this->createExportAction($inputOptions);
        $this->assertEquals($inputOptions['acl_resource'], $exportAction->getAclResource());
    }
}
