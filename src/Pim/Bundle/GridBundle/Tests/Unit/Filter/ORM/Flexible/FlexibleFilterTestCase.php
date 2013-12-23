<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Translation\TranslatorInterface;

use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Pim\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleFilter;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class FlexibleFilterTestCase extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_ALIAS         = 'test_alias';
    const TEST_FIELD         = 'test_field';
    const TEST_FLEXIBLE_NAME = 'test_flexible_entity';
    /**#@-*/

    /**
     * @var AbstractFlexibleFilter
     */
    protected $model;

    /**
     * @var FlexibleEntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleEntityRepository;

    /**
     * @var FlexibleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleManager;

    /**
     * @var FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markTestIncomplete('This test must be completed after flexible repository refactoring.');

        $this->flexibleEntityRepository = $this->createFlexibleEntityRepository();
        $this->flexibleManager = $this->createFlexibleManager($this->flexibleEntityRepository);
        $this->flexibleRegistry = $this->createFlexibleRegistry(
            $this->flexibleManager,
            self::TEST_FLEXIBLE_NAME
        );

        $this->model = $this->createTestFilter($this->flexibleRegistry);
    }

    /**
     * @return FlexibleEntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFlexibleEntityRepository()
    {
        return $this->getMockBuilder(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository'
        )->setMethods(array('applyFilterByAttribute'))->disableOriginalConstructor()->getMock();
    }

    /**
     * @param FlexibleEntityRepository $entityRepository
     *
     * @return FlexibleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFlexibleManager(FlexibleEntityRepository $entityRepository)
    {
        $flexibleManager = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->setMethods(
                array(
                    'getFlexibleRepository', 'getAttributeRepository',
                    'getFlexibleName', 'getAttributeOptionRepository'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $flexibleManager->expects($this->any())
            ->method('getFlexibleRepository')
            ->will($this->returnValue($entityRepository));

        $flexibleManager->expects($this->any())
            ->method('getFlexibleName')
            ->will($this->returnValue(self::TEST_FLEXIBLE_NAME));

        return $flexibleManager;
    }

    /**
     * @param FlexibleManager $flexibleManager
     * @param string          $flexibleName
     *
     * @return FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFlexibleRegistry(FlexibleManager $flexibleManager, $flexibleName)
    {
        $flexibleRegistry = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry',
            array('getManager')
        );
        $flexibleRegistry->expects($this->any())
            ->method('getManager')
            ->with($flexibleName)
            ->will($this->returnValue($flexibleManager));

        return $flexibleRegistry;
    }

    /**
     * @param FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject $flexibleRegistry
     *
     * @return AbstractFlexibleFilter
     */
    abstract protected function createTestFilter($flexibleRegistry);

    /**
     * @return array
     */
    abstract public function filterDataProvider();

    /**
     * @param array $data
     * @param array $expectRepositoryCalls
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter(array $data, array $expectRepositoryCalls)
    {
        $queryBuilder = $this->createQueryBuilder();
        $proxyQuery = $this->createProxyQuery($queryBuilder);

        $this->addFlexibleEntityRepositoryExpectedCalls(
            $queryBuilder,
            $expectRepositoryCalls
        );

        $this->initializeFlexibleFilter($this->model);
        $this->model->filter($proxyQuery, self::TEST_ALIAS, self::TEST_FIELD, $data);
    }

    /**
     * @param AbstractFlexibleFilter $filter
     * @param array                  $options
     */
    protected function initializeFlexibleFilter(AbstractFlexibleFilter $filter, array $options = array())
    {
        $filter->initialize(self::TEST_NAME, $this->getFilterInitializeOptions($options));
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getFilterInitializeOptions(array $options = array())
    {
        return array_merge(
            $options,
            array(
                'flexible_name' => self::TEST_FLEXIBLE_NAME,
                'field_name' => self::TEST_FIELD
            )
        );
    }

    /**
     * @return QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createQueryBuilder()
    {
        return $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @param mixed $queryBuilder
     *
     * @return ProxyQuery
     */
    protected function createProxyQuery($queryBuilder = null)
    {
        if (!$queryBuilder) {
            $queryBuilder = $this->createQueryBuilder();
        }

        return new ProxyQuery($queryBuilder);
    }

    /**
     * @param mixed $queryBuilder
     * @param array $expectedCalls
     */
    protected function addFlexibleEntityRepositoryExpectedCalls($queryBuilder, array $expectedCalls)
    {
        $index = 0;
        if ($expectedCalls) {
            foreach ($expectedCalls as $expectedCall) {
                list($method, $arguments, $result) = $expectedCall;

                if ($method == 'applyFilterByAttribute') {
                    array_unshift($arguments, $queryBuilder);
                }

                $methodExpectation = $this->flexibleEntityRepository->expects($this->at($index++))->method($method);
                $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                $methodExpectation->will($this->returnValue($result));
            }
        } else {
            $this->flexibleEntityRepository->expects($this->never())->method($this->anything());
        }
    }

    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTranslatorMock()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())->method('trans')->will($this->returnArgument(0));

        return $translator;
    }
}
