<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Owner\OwnerTree;

class OwnerTreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider addBusinessUnitRelationProvider
     */
    public function testAddBusinessUnitRelation($src, $expected)
    {
        $tree = new OwnerTree();
        foreach ($src as $item) {
            $tree->addBusinessUnitRelation($item[0], $item[1]);
        }

        foreach ($expected as $buId => $sBuIds) {
            $this->assertEquals(
                $sBuIds,
                $tree->getSubordinateBusinessUnitIds($buId),
                sprintf('Failed for %s', $buId)
            );
        }
    }

    public function testAddBusinessUnit()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu1', null);
        $this->assertNull($tree->getBusinessUnitOrganizationId('bu1'));
        $this->assertEquals(array(), $tree->getOrganizationBusinessUnitIds('bu1'));

        $tree->addBusinessUnit('bu2', 'org');
        $this->assertEquals('org', $tree->getBusinessUnitOrganizationId('bu2'));
        $this->assertEquals(array('bu2'), $tree->getOrganizationBusinessUnitIds('org'));

        $tree->addBusinessUnit('bu3', 'org');
        $this->assertEquals('org', $tree->getBusinessUnitOrganizationId('bu3'));
        $this->assertEquals(array('bu2', 'bu3'), $tree->getOrganizationBusinessUnitIds('org'));
    }

    public function testAddUser()
    {
        $tree = new OwnerTree();

        $tree->addUser('user1', null);
        $this->assertNull($tree->getUserBusinessUnitId('user1'));

        $tree->addUser('user2', 'bu');
        $this->assertEquals('bu', $tree->getUserBusinessUnitId('user2'));
        $this->assertEquals(array('user2'), $tree->getBusinessUnitUserIds('bu'));

        $tree->addUser('user3', 'bu');
        $this->assertEquals('bu', $tree->getUserBusinessUnitId('user3'));
        $this->assertEquals(array('user2', 'user3'), $tree->getBusinessUnitUserIds('bu'));
    }

    public function testAddUserGetsOrganizationIdFromBusinessUnit()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', 'org');

        $tree->addUser('user1', null);
        $this->assertNull($tree->getUserBusinessUnitId('user1'));
        $this->assertNull($tree->getUserOrganizationId('user1'));

        $tree->addUser('user2', 'bu');
        $this->assertEquals('bu', $tree->getUserBusinessUnitId('user2'));
        $this->assertEquals('org', $tree->getUserOrganizationId('user2'));
    }

    public function testAddBusinessUnitSetsUserOrganizationId()
    {
        $tree = new OwnerTree();

        $tree->addUser('user1', null);
        $tree->addUser('user2', 'bu2');

        $tree->addBusinessUnit('bu1', null);
        $this->assertNull($tree->getBusinessUnitOrganizationId('bu1'));
        $this->assertNull($tree->getUserOrganizationId('user1'));
        $this->assertNull($tree->getUserOrganizationId('user2'));


        $tree->addBusinessUnit('bu2', 'org');
        $this->assertEquals('org', $tree->getBusinessUnitOrganizationId('bu2'));
        $this->assertNull($tree->getUserOrganizationId('user1'));
        $this->assertEquals('org', $tree->getUserOrganizationId('user2'));
    }

    public function testAddUserBusinessUnit()
    {
        $tree = new OwnerTree();

        $tree->addUserBusinessUnit('user1', null);
        $this->assertEquals(array(), $tree->getUserBusinessUnitIds('user1'));

        $tree->addUserBusinessUnit('user1', 'bu1');
        $this->assertEquals(array('bu1'), $tree->getUserBusinessUnitIds('user1'));

        $tree->addUserBusinessUnit('user1', 'bu2');
        $this->assertEquals(array('bu1', 'bu2'), $tree->getUserBusinessUnitIds('user1'));
    }

    public static function addBusinessUnitRelationProvider()
    {
        return array(
            '1: [null]' => array(
                array(
                    array('1', null),
                ),
                array(
                    '1' => array(),
                )
            ),
            '1: [11, 12]' => array(
                array(
                    array('1', null),
                    array('11', '1'),
                    array('12', '1'),
                ),
                array(
                    '1' => array('11', '12'),
                    '11' => array(),
                    '12' => array(),
                )
            ),
            '1: [11, 12] reverse' => array(
                array(
                    array('12', '1'),
                    array('11', '1'),
                    array('1', null),
                ),
                array(
                    '1' => array('12', '11'),
                    '11' => array(),
                    '12' => array(),
                )
            ),
            '1: [11: [111], 12]' => array(
                array(
                    array('1', null),
                    array('11', '1'),
                    array('111', '11'),
                    array('12', '1'),
                ),
                array(
                    '1' => array('11', '111', '12'),
                    '11' => array('111'),
                    '111' => array(),
                    '12' => array(),
                )
            ),
            '1: [11: [111: [1111, 1112]], 12: [121, 122: [1221]]]' => array(
                array(
                    array('1', null),
                    array('11', '1'),
                    array('111', '11'),
                    array('1111', '111'),
                    array('1112', '111'),
                    array('12', '1'),
                    array('121', '12'),
                    array('122', '12'),
                    array('1221', '122'),
                ),
                array(
                    '1' => array('11', '111', '1111', '1112', '12', '121', '122', '1221'),
                    '11' => array('111', '1111', '1112'),
                    '111' => array('1111', '1112'),
                    '1111' => array(),
                    '1112' => array(),
                    '12' => array('121', '122', '1221'),
                    '121' => array(),
                    '122' => array('1221'),
                )
            ),
        );
    }
}
