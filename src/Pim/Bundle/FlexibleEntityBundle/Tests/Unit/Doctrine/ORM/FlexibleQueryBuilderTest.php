<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractOrmTest;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleQueryBuilderTest extends AbstractOrmTest
{
    /**
     * @var FlexibleQueryBuilder
     */
    protected $queryBuilder;

    /**
     * Prepare test
     */
    protected function setUp()
    {
        parent::setUp();

        $qb = new QueryBuilder($this->entityManager);
        $this->queryBuilder = new FlexibleQueryBuilder($qb, 'en', 'ecommerce');
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        $code = 'fr';
        $this->queryBuilder->setLocale($code);
        $this->assertEquals($this->queryBuilder->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        $code = 'ecommerce';
        $this->queryBuilder->setScope($code);
        $this->assertEquals($this->queryBuilder->getScope(), $code);
    }

    /**
     * Test related method
     * @expectedException \Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException
     */
    public function testGetAllowedOperatorsException()
    {
        $this->queryBuilder->getAllowedOperators('unknowBackendType');
    }

    /**
     * Test related method
     */
    public function testGetAllowedOperators()
    {
        $operators = $this->queryBuilder->getAllowedOperators(AbstractAttributeType::BACKEND_TYPE_INTEGER);
        $this->assertEquals($operators, array('=', '<', '<=', '>', '>='));
    }

    /**
     * Test related method
     */
    public function testPrepareAttributeJoinCondition()
    {
        $this->queryBuilder->setLocale('fr');
        $this->queryBuilder->setScope('eco');

        $attribute = new Attribute();
        $attribute->setId(12);
        $condition = $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
        $this->assertEquals($condition, 'alias.attribute = 12');

        $attribute->setTranslatable(true);
        $condition = $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
        $this->assertEquals($condition, "alias.attribute = 12 AND alias.locale = 'fr'");

        $attribute->setScopable(true);
        $condition = $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
        $this->assertEquals($condition, "alias.attribute = 12 AND alias.locale = 'fr' AND alias.scope = 'eco'");
    }

    /**
     * Test related method
     * @expectedException \Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException
     */
    public function testPrepareAttributeJoinConditionExceptionLocale()
    {
        $attribute = new Attribute();
        $attribute->setTranslatable(true);
        $this->queryBuilder->setLocale(null);
        $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
    }

    /**
     * Test related method
     * @expectedException \Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException
     */
    public function testPrepareAttributeJoinConditionExceptionScope()
    {
        $attribute = new Attribute();
        $attribute->setScopable(true);
        $this->queryBuilder->setScope(null);
        $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
    }

    /**
     * Data provider
     *
     * @return multitype:multitype:number string
     *
     * @static
     */
    public static function criteriaProvider()
    {
        return array(
            array('code', '=', 'value', "code = 'value'"),
            array('code', '<', 'value', "code < 'value'"),
            array('code', '<=', 'value', "code <= 'value'"),
            array('code', '>', 'value', "code > 'value'"),
            array('code', '>=', 'value', "code >= 'value'"),
            array('code', 'LIKE', 'value', "code LIKE 'value'"),
            array('code', 'NOT LIKE', 'value', "code NOT LIKE 'value'"),
            array('code', 'NULL', null, "code IS NULL"),
            array('code', 'NOT NULL', null, "code IS NOT NULL"),
            array('code', 'IN', array('a', 'b'), "code IN('a', 'b')"),
            array('code', 'NOT IN', array('a', 'b'), "code NOT IN('a', 'b')")
        );
    }

    /**
     * Test related method
     *
     * @param string       $field    the backend field name
     * @param string       $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     * @param string       $expected the expected result
     *
     * @dataProvider criteriaProvider
     */
    public function testPrepareCriteriaCondition($field, $operator, $value, $expected)
    {
        $result = $this->queryBuilder->prepareCriteriaCondition($field, $operator, $value);
        $this->assertEquals($result, $expected);
    }

    /**
     * Test related method
     * @expectedException \Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException
     */
    public function testPrepareCriteriaConditionException()
    {
        $this->queryBuilder->prepareCriteriaCondition('code', 'UNKNOWN OPERATOR', 'value');
    }
}
