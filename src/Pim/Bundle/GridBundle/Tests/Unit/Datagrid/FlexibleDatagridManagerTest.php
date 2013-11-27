<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Datagrid;

use Pim\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleDatagridManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_FLEXIBLE_NAME = 'test_flexible_name';
    const TEST_LOCALE        = 'test_locale';
    const TEST_SCOPE         = 'test_scope';

    /**
     * @var FlexibleDatagridManager
     */
    protected $model;

    /**
     * @var array
     */
    protected $testAttributes = array('attribute_1', 'attribute_2');

    protected function setUp()
    {
        $this->model = $this->getMockForAbstractClass('Pim\Bundle\GridBundle\Datagrid\FlexibleDatagridManager');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetFlexibleManager()
    {
        $parametersMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ParametersInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getLocale', 'getScope')
        );
        $parametersMock->expects($this->once())->method('getLocale')->will($this->returnValue(self::TEST_LOCALE));
        $parametersMock->expects($this->once())->method('getScope')->will($this->returnValue(self::TEST_SCOPE));

        $this->model->setParameters($parametersMock);

        $flexibleManagerMock = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array('setLocale', 'setScope'),
            array(),
            '',
            false
        );
        $flexibleManagerMock->expects($this->once())->method('setLocale')->with(self::TEST_LOCALE);
        $flexibleManagerMock->expects($this->once())->method('setScope')->with(self::TEST_SCOPE);

        $this->assertAttributeEmpty('flexibleManager', $this->model);
        $this->model->setFlexibleManager($flexibleManagerMock);
        $this->assertAttributeEquals($flexibleManagerMock, 'flexibleManager', $this->model);
    }
}
