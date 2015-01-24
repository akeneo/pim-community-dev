<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilySorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\FamilySorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface');
    }

    function it_supports_the_family_field()
    {
        $this->supportsField('family')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_adds_a_sorter_to_the_query($qb)
    {
        $qb->getRootAlias()->willReturn('r');

        $qb->leftJoin('r.family', 'sorterfamily')->shouldBeCalled()->willReturn($qb);
        $qb
            ->leftJoin(
                'sorterfamily.translations',
                'sorterfamilyTranslations',
                'WITH',
                'sorterfamilyTranslations.locale = :dataLocale'
            )
            ->shouldBeCalled()
            ->willReturn($qb)
        ;
        $qb
            ->addSelect(
                'COALESCE(sorterfamilyTranslations.label, CONCAT(\'[\', sorterfamily.code, \']\')) as sorterfamilyLabel'
            )
            ->shouldBeCalled()
            ->willReturn($qb)
        ;
        $qb->addOrderBy('sorterfamilyLabel', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addFieldSorter('family', 'DESC');
    }
}
