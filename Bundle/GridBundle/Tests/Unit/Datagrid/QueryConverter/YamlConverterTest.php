<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\QueryConverter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\GridBundle\Datagrid\QueryConverter\YamlConverter;

use Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM\Stub\Entity;

class YamlConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var YamlConverter
     */
    protected $converter;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $this->converter = new YamlConverter();
        $this->em        = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);

        $this->em
            ->expects($this->any())
            ->method('createQueryBuilder')
            ->withAnyParameters()
            ->will($this->returnValue(new QueryBuilder($this->em)));

    }

    protected function tearDown()
    {
        unset($this->converter, $this->em);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testParseException()
    {
        $this->converter->parse(array(), $this->em);
    }

    public function testParse()
    {
        $value = array(
            'name'     => 'Test Report',
            'from'     => array(
                array(
                    'table' => 'Doctrine\Tests\Models\CMS\CmsUser',
                    'alias' => 'u',
                )
            ),
            'select'   => 'u',
            'distinct' => true,
            'join'     => array(
                'inner' => array(
                    array(
                        'join'  => 'u.articles',
                        'alias' => 'a'
                    ),
                ),
                'left' => array(
                    array(
                        'join'  => 'email',
                        'alias' => 'e'
                    )
                )
            ),
            'groupBy'  => 'u.id',
            'having'   => 'COUNT(u.id) > 0',
            'where'    => array(
                'and'  => array(
                    'u.status IS NULL'
                ),
                'or'   => array(
                    'u.id < 100'
                ),
            ),
            'orderBy'  => array(
                array(
                    'column' => 'u.id',
                    'dir'    => 'desc',
                ),
            ),
        );

        $qb = $this->converter->parse($value, $this->em);

        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $qb);
        $this->assertNotEmpty($qb->getDQLPart('select'));
        $this->assertNotEmpty($qb->getDQLPart('from'));
        $this->assertNotEmpty($qb->getDQLPart('orderBy'));
        $this->assertTrue($qb->getDQLPart('distinct'));

        $value = '
name:   "Test Report"
select: "u"
from:
    - { table: Doctrine\Tests\Models\CMS\CmsUser, alias: u }';

        $qb = $this->converter->parse($value, $this->em);

        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $qb);
    }

    public function testDump()
    {
        $qb = new QueryBuilder($this->em);

        $result = $this->converter->dump($qb);

        $this->assertInternalType('string', $result);
    }
}
