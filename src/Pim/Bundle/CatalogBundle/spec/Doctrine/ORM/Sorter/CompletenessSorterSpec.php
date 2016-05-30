<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompletenessSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->setQueryBuilder($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\CompletenessSorter');
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Sorter\FieldSorterInterface');
    }

    function it_supports_the_completeness_field()
    {
        $this->supportsField('completeness')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_adds_a_sorter_to_the_query($qb, EntityManagerInterface $em, ClassMetadata $md)
    {
        $em->getClassMetadata(Argument::any())->willReturn($md);
        $md->getAssociationMapping('completenesses')
            ->shouldBeCalled()
            ->willReturn(['targetEntity' => 'CompletenessClass'])
        ;
        $qb->getRootAlias()->willReturn('r');
        $qb->getRootAliases()->willReturn(['r']);

        $qb->getRootEntities()->willReturn(['rootEntity']);
        $qb->getEntityManager()->willReturn($em);
        $qb
            ->leftJoin(
                'PimCatalogBundle:Locale',
                'sorterCompletenessLocale',
                'WITH',
                Argument::any()
            )
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb
            ->leftJoin(
                'PimCatalogBundle:Channel',
                'sorterCompletenessChannel',
                'WITH',
                'sorterCompletenessChannel.code = :cScopeCode'
            )
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb
            ->leftJoin(
                'CompletenessClass',
                'sorterCompleteness',
                'WITH',
                'sorterCompleteness.product = r.id AND '.
                'sorterCompleteness.channel = sorterCompletenessChannel.id AND '.
                'sorterCompleteness.locale = sorterCompletenessLocale.id'
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->setParameter(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($qb);

        $qb->addOrderBy('sorterCompleteness.ratio', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addFieldSorter('completeness', 'DESC', 'en_US', 'mobile');
    }
}
