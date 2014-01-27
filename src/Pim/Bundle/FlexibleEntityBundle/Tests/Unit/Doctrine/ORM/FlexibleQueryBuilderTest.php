<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractOrmTest;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;

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
}
