<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Owner\OwnerTree;

class OwnerTreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider addBusinessUnitHierarchyProvider
     */
    public function testAddAndRemoveBusinessUnitHierarchy($businessUnitId, $treeOfChildBusinessUnitIds, $expected)
    {
        $tree = new OwnerTree();
        $tree->addBusinessUnitHierarchy($businessUnitId, $treeOfChildBusinessUnitIds);

        foreach ($expected as $buId => $sBuIds) {
            $this->assertEquals(
                $sBuIds,
                $tree->getSubordinateBusinessUnitIds($buId),
                sprintf('Failed for %s', $buId)
            );
        }

        $tree->removeBusinessUnitHierarchy($businessUnitId);
        $this->assertTrue($tree->isEmpty());
    }

    public function testAddAndRemoveBusinessUnit()
    {
        $tree = new OwnerTree();
        $tree->addBusinessUnit('bu', 'org');
        $tree->addBusinessUnitHierarchy('bu', array('bu1' => null));
        $tree->addUser('usr', 'org', 'bu', array('bu1'));

        $this->assertEquals('org', $tree->getUserOrganizationId('usr'));

        $tree->removeBusinessUnit('bu');

        $this->assertFalse($tree->isEmpty());
        $this->assertNull($tree->getUserOrganizationId('usr'));
        $this->assertNull($tree->getUserBusinessUnitId('usr'));
        $this->assertEquals(array(), $tree->getUserBusinessUnitIds('usr'));
        $this->assertNull($tree->getBusinessUnitOrganizationId('bu'));
    }

    public function testRemoveBusinessUnitHierarchyShouldClearHierarchyDataOnly()
    {
        $tree = new OwnerTree();
        $tree->addBusinessUnitHierarchy('bu', array('bu1' => null));
        $tree->addUser('usr', 'org', 'bu', array('bu1'));
        $tree->addBusinessUnit('bu', 'org');
        $tree->removeBusinessUnitHierarchy('bu');

        $this->assertFalse($tree->isEmpty());
        $this->assertEquals('org', $tree->getUserOrganizationId('usr'));
        $this->assertEquals('bu', $tree->getUserBusinessUnitId('usr'));
        $this->assertEquals(array('bu1'), $tree->getUserBusinessUnitIds('usr'));
        $this->assertEquals('org', $tree->getBusinessUnitOrganizationId('bu'));
    }

    /**
     * @dataProvider addUserProvider
     */
    public function testAddAndRemoveUser($userId, $organizationId, $businessUnitId, $businessUnitIds)
    {
        $tree = new OwnerTree();
        $tree->addUser($userId, $organizationId, $businessUnitId, $businessUnitIds);

        $this->assertEquals($organizationId, $tree->getUserOrganizationId($userId));
        $this->assertEquals($businessUnitId, $tree->getUserBusinessUnitId($userId));
        $this->assertEquals(
            !empty($businessUnitIds) ? $businessUnitIds : array(),
            $tree->getUserBusinessUnitIds($userId)
        );

        $tree->removeUser($userId);
        $this->assertTrue($tree->isEmpty());
    }

    public function testRemoveOrganization()
    {
        $tree = new OwnerTree();
        $tree->addUser('usr', 'org', 'bu', null);
        $tree->addBusinessUnit('bu', 'org');

        $this->assertEquals('org', $tree->getUserOrganizationId('usr'));
        $this->assertEquals('org', $tree->getBusinessUnitOrganizationId('bu'));

        $tree->removeOrganization('org');

        $this->assertNull($tree->getUserOrganizationId('usr'));
        $this->assertNull($tree->getBusinessUnitOrganizationId('bu'));
    }

    public static function addBusinessUnitHierarchyProvider()
    {
        return array(
            '1: [null]' => array(
                '1',
                null,
                array(
                    '1' => array(),
                )
            ),
            '1: [array()]' => array(
                '1',
                array(),
                array(
                    '1' => array(),
                )
            ),
            '1: [11, 12]' => array(
                '1',
                array(
                    '11' => null,
                    '12' => null
                ),
                array(
                    '1' => array('11', '12'),
                    '11' => array(),
                    '12' => array(),
                )
            ),
            '1: [11: [111], 12]' => array(
                '1',
                array(
                    '11' => array(
                        '111' => null,
                    ),
                    '12' => null
                ),
                array(
                    '1' => array('11', '111', '12'),
                    '11' => array('111'),
                    '12' => array(),
                )
            ),
            '1: [11: [111: [1111, 1112]], 12: [121, 122: [1221]]]' => array(
                '1',
                array(
                    '11' => array(
                        '111' => array(
                            '1111' => null,
                            '1112' => array(),
                        ),
                    ),
                    '12' => array(
                        '121' => null,
                        '122' => array(
                            '1221' => null
                        ),
                    ),
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

    public static function addUserProvider()
    {
        return array(
            array(
                'user1', null, null, null
            ),
            array(
                'user1', 'org1', null, null
            ),
            array(
                'user1', null, 'bu1', null
            ),
            array(
                'user1', 'org1', 'bu1', null
            ),
            array(
                'user1', 'org1', 'bu1', array()
            ),
            array(
                'user1', 'org1', 'bu1', array('bu1', 'bu2')
            ),
        );
    }
}
