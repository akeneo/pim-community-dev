<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Datagrid;

use Oro\Bundle\SearchBundle\Datagrid\SearchDatagridBuilder;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

class SearchDatagridBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatePager()
    {
        $datagridBuilder = $this->createDatagridBuilder();

        $indexerQuery = $this->getMock('Oro\Bundle\SearchBundle\Datagrid\IndexerQuery', array(), array(), '', false);
        $fieldCollection = new FieldDescriptionCollection();
        $routeGenerator = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Route\RouteGeneratorInterface',
            array(),
            '',
            false
        );
        $parameters = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ParametersInterface',
            array(),
            '',
            false
        );

        $datagrid = $datagridBuilder->getBaseDatagrid(
            $indexerQuery,
            $fieldCollection,
            $routeGenerator,
            $parameters,
            'name',
            'hint'
        );
        $pager = $datagrid->getPager();

        $this->assertInstanceOf('Oro\Bundle\SearchBundle\Datagrid\IndexerPager', $pager);
        $this->assertAttributeEquals($indexerQuery, 'query', $pager);
    }

    /**
     * @return SearchDatagridBuilder
     */
    protected function createDatagridBuilder()
    {
        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilder', array(), array(), '', false);
        $formFactory = $this->getMockForAbstractClass(
            'Symfony\Component\Form\FormFactoryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('createNamedBuilder')
        );
        $formFactory->expects($this->any())
            ->method('createNamedBuilder')
            ->will($this->returnValue($formBuilder));
        $eventDispatcher = $this->getMockForAbstractClass(
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            array(),
            '',
            false
        );
        $securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()->getMock();
        $filterFactory = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\FilterFactoryInterface',
            array(),
            '',
            false
        );
        $sorterFactory = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface',
            array(),
            '',
            false
        );
        $actionFactory = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Action\ActionFactoryInterface',
            array(),
            '',
            false
        );

        return new SearchDatagridBuilder(
            $formFactory,
            $eventDispatcher,
            $securityFacade,
            $filterFactory,
            $sorterFactory,
            $actionFactory,
            'Oro\Bundle\GridBundle\Datagrid\Datagrid'
        );
    }
}
