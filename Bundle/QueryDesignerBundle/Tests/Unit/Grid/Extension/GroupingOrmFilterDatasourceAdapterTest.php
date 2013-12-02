<?php

namespace Oro\Bundle\QueryDesignerBundle\Tests\Unit\Grid\Extension;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Tests\OrmTestCase;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\QueryDesignerBundle\Grid\Extension\GroupingOrmFilterDatasourceAdapter;

class GroupingOrmFilterDatasourceAdapterTest extends OrmTestCase
{
    public function testNoRestrictions()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0',
            $qb->getDQL()
        );
    }

    public function testOneRestriction()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND u.name = 1',
            $qb->getDQL()
        );
    }

    public function testSeveralRestrictions()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '2'), FilterUtility::CONDITION_OR);
        $ds->addRestriction($qb->expr()->eq('u.name', '3'), FilterUtility::CONDITION_AND);
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND '
            . '((u.name = 1 OR u.name = 2) AND u.name = 3)',
            $qb->getDQL()
        );
    }

    public function testEmptyGroup()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->endRestrictionGroup();
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0',
            $qb->getDQL()
        );
    }

    public function testOneRestrictionInGroup()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->endRestrictionGroup();
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND u.name = 1',
            $qb->getDQL()
        );
    }

    public function testSeveralRestrictionsInGroup()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '2'), FilterUtility::CONDITION_OR);
        $ds->addRestriction($qb->expr()->eq('u.name', '3'), FilterUtility::CONDITION_AND);
        $ds->endRestrictionGroup();
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND '
            . '((u.name = 1 OR u.name = 2) AND u.name = 3)',
            $qb->getDQL()
        );
    }

    public function testNestedGroupsWithOneRestrictionInNestedGroup()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        //  src: (1 OR (2))
        // dest: (1 OR 2)
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_OR);
        $ds->addRestriction($qb->expr()->eq('u.name', '2'), FilterUtility::CONDITION_AND);
        $ds->endRestrictionGroup();
        $ds->endRestrictionGroup();
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND '
            . '(u.name = 1 OR u.name = 2)',
            $qb->getDQL()
        );
    }

    public function testNestedGroupsWithSameCondition()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        //  src: (1 OR (2 OR 3))
        // dest: (1 OR (2 OR 3))
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_OR);
        $ds->addRestriction($qb->expr()->eq('u.name', '2'), FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '3'), FilterUtility::CONDITION_OR);
        $ds->endRestrictionGroup();
        $ds->endRestrictionGroup();
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND '
            . '(u.name = 1 OR (u.name = 2 OR u.name = 3))',
            $qb->getDQL()
        );
    }

    public function testNestedGroupsWithDifferentConditions()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        //  src: (1 OR (2 AND 3))
        // dest: (1 OR (2 AND 3))
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_OR);
        $ds->addRestriction($qb->expr()->eq('u.name', '2'), FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '3'), FilterUtility::CONDITION_AND);
        $ds->endRestrictionGroup();
        $ds->endRestrictionGroup();
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND '
            . '(u.name = 1 OR (u.name = 2 AND u.name = 3))',
            $qb->getDQL()
        );
    }

    public function testComplexExpr()
    {
        $qb = new QueryBuilder($this->_getTestEntityManager());
        $qb->select(['u.id'])
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where('u.id = 0');
        $ds = new GroupingOrmFilterDatasourceAdapter($qb);

        //  src: (1 AND ((2 AND (3 OR 4)) OR (5) OR (6 AND 7)) AND 8)
        // dest: (1 AND ((2 AND (3 OR 4)) OR 5 OR (6 AND 7)) AND 8)
        $ds->addRestriction($qb->expr()->eq('u.name', '1'), FilterUtility::CONDITION_AND);
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '2'), FilterUtility::CONDITION_AND);
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '3'), FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '4'), FilterUtility::CONDITION_OR);
        $ds->endRestrictionGroup();
        $ds->endRestrictionGroup();
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_OR);
        $ds->addRestriction($qb->expr()->eq('u.name', '5'), FilterUtility::CONDITION_AND);
        $ds->endRestrictionGroup();
        $ds->beginRestrictionGroup(FilterUtility::CONDITION_OR);
        $ds->addRestriction($qb->expr()->eq('u.name', '6'), FilterUtility::CONDITION_AND);
        $ds->addRestriction($qb->expr()->eq('u.name', '7'), FilterUtility::CONDITION_AND);
        $ds->endRestrictionGroup();
        $ds->endRestrictionGroup();
        $ds->addRestriction($qb->expr()->eq('u.name', '8'), FilterUtility::CONDITION_AND);
        $ds->applyRestrictions();

        $this->assertEquals(
            'SELECT u.id FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.id = 0 AND '
            . '(u.name = 1 AND '
            . '((u.name = 2 AND (u.name = 3 OR u.name = 4)) OR u.name = 5 OR (u.name = 6 AND u.name = 7)) AND '
            . 'u.name = 8)',
            $qb->getDQL()
        );
    }
}
