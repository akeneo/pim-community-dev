<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Doctrine\ORM\Query\Expr;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class FilterTestCase extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME  = 'test_name';
    const TEST_ALIAS = 'test_alias';
    const TEST_FIELD = 'test_field';
    /**#@-*/

    /**
     * @var AbstractFilter
     */
    protected $model;

    /**
     * @var Expr
     */
    protected $expressionFactory;

    protected function setUp()
    {
        $this->model = $this->createTestFilter();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @return FilterInterface
     */
    abstract protected function createTestFilter();

    /**
     * @return array
     */
    abstract public function filterDataProvider();

    /**
     * @dataProvider filterDataProvider
     *
     * @param array $data
     * @param array $expectProxyQueryCalls
     * @param array $options
     */
    public function testFilter($data, array $expectProxyQueryCalls, array $options = array())
    {
        $proxyQuery = $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery')
            ->setMethods(
                array('getUniqueParameterId', 'andWhere', 'orWhere', 'andHaving', 'orHaving', 'setParameter')
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->addProxyQueryExpectedCalls($proxyQuery, $expectProxyQueryCalls);

        $options = array_merge(array('field_mapping' => true), $options);

        $this->model->initialize(self::TEST_NAME, $options);
        $this->model->filter($proxyQuery, self::TEST_ALIAS, self::TEST_FIELD, $data);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $proxyQuery
     * @param array $expectedCalls
     */
    private function addProxyQueryExpectedCalls($proxyQuery, array $expectedCalls)
    {
        $index = 0;
        if ($expectedCalls) {
            foreach ($expectedCalls as $expectedCall) {
                list($method, $arguments, $result) = $expectedCall;

                $methodExpectation = $proxyQuery->expects($this->at($index++))->method($method);
                $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                $methodExpectation->will($this->returnValue($result));
            }
        } else {
            $proxyQuery->expects($this->never())->method($this->anything());
        }
    }

    /**
     * @return Expr
     */
    protected function getExpressionFactory()
    {
        if (!$this->expressionFactory) {
            $this->expressionFactory = new Expr();
        }
        return $this->expressionFactory;
    }

    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTranslatorMock()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));
        return $translator;
    }
}
