<?php

namespace Pim\Bundle\CustomEntityBundle\Tests\Unit\ControllerWorker;

use Pim\Bundle\CustomEntityBundle\ControllerWorker\DatagridWorker;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridWorkerTest extends AbstractWorkerTest
{
    protected $datagridHelper;
    protected $worker;

    protected function setUp()
    {
        parent::setUp();
        $this->datagridHelper = $this->getMock('Pim\Bundle\GridBundle\Helper\DatagridHelperInterface');
        $this->worker = new DatagridWorker(
            $this->formFactory,
            $this->templating,
            $this->router,
            $this->translator,
            $this->datagridHelper
        );
    }

    public function getIndexActionData()
    {
        return array(
            'html' => array('html'),
            'json' => array('json')
        );
    }

    /**
     * @dataProvider getIndexActionData
     */
    public function testIndexAction($requestFormat)
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())
            ->method('getRequestFormat')
            ->will($this->returnValue($requestFormat));
        $this->configuration
            ->expects($this->any())
            ->method('getDatagridNamespace')
            ->will($this->returnValue('datagrid_namespace'));
        $this->configuration
            ->expects($this->any())
            ->method('getIndexTemplate')
            ->will($this->returnValue('index_template'));
        $this->configuration
            ->expects($this->any())
            ->method('getQueryBuilderOptions')
            ->will($this->returnValue(array('query_builder_options')));
        $this->manager->expects($this->once())
            ->method('createQueryBuilder')
            ->with($this->equalTo('entity_class'), $this->equalTo(array('query_builder_options')))
            ->will($this->returnValue($queryBuilder));
        $routeGenerator = $this->getMock('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $routeGenerator->expects($this->once())
            ->method('setRouteParameters')
            ->with($this->equalTo(array('customEntityName' => 'name')));
        $datagrid = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $datagrid->expects($this->once())
            ->method('createView')
            ->will($this->returnValue('datagrid_view'));
        $datagrid->expects($this->any())
            ->method('getRouteGenerator')
            ->will($this->returnValue($routeGenerator));
        $this->datagridHelper
            ->expects($this->once())
            ->method('getDatagrid')
            ->with(
                $this->equalTo('name'),
                $this->identicalTo($queryBuilder),
                $this->equalTo('datagrid_namespace')
            )
            ->will($this->returnValue($datagrid));

        $this->assertRendered(
            ('json' == $requestFormat) ? 'OroGridBundle:Datagrid:list.json.php' : 'index_template',
            array('datagrid' => 'datagrid_view')
        );
        $result = $this->worker->indexAction($this->configuration, $this->request);
    }
}
