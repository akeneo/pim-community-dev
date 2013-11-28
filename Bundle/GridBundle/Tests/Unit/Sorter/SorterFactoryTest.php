<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Sorter;

use Oro\Bundle\GridBundle\Sorter\SorterFactory;
use Oro\Bundle\GridBundle\Field\FieldDescription;

class SorterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME                    = 'name';
    const TEST_SORTER_SERVICE          = 'oro_grid.sorter';
    /**#@-*/

    /**
     * @var SorterFactory
     */
    protected $model;

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The field name must be defined for sorter
     */
    public function testCreateNoField()
    {
        $containerMock = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $fieldDescriptionMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Field\FieldDescriptionInterface');

        $this->model = new SorterFactory($containerMock);

        $this->model->create($fieldDescriptionMock);
    }

    /**
     * @param $serviceName
     * @dataProvider getFieldOptionsDataProvider
     */
    public function testCreate($serviceName)
    {
        $sorterMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Sorter\SorterInterface',
            array(),
            '',
            false
        );

        $containerMock = $this->getMockForAbstractClass(
            'Symfony\Component\DependencyInjection\ContainerInterface',
            array(),
            '',
            false,
            true,
            true,
            array('get')
        );

        $containerMock->expects($this->once())
            ->method('get')
            ->with($serviceName)
            ->will($this->returnValue($sorterMock));

        $this->model = new SorterFactory($containerMock);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName(self::TEST_NAME);

        $sorter = $this->model->create($fieldDescription);

        $this->assertEquals($sorterMock, $sorter);
    }

    public function getFieldOptionsDataProvider()
    {
        return array(
            'is_regular' => array(
                '$serviceName'  => self::TEST_SORTER_SERVICE
            )
        );
    }
}
