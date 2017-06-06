<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;

class BaseSorterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->setQueryBuilder($qb);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Query\Sorter\FieldSorterInterface');
    }

    function it_supports_fields()
    {
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('created')->shouldReturn(true);
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField(Argument::any())->shouldReturn(false);
    }

    function it_supports_attributes(
        AttributeInterface $identifier,
        AttributeInterface $text,
        AttributeInterface $textarea,
        AttributeInterface $number,
        AttributeInterface $boolean,
        AttributeInterface $date,
        AttributeInterface $other
    ) {
        $identifier->getType()->willReturn('pim_catalog_identifier');
        $this->supportsAttribute($identifier)->shouldReturn(true);

        $text->getType()->willReturn('pim_catalog_text');
        $this->supportsAttribute($text)->shouldReturn(true);

        $textarea->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textarea)->shouldReturn(true);

        $number->getType()->willReturn('pim_catalog_number');
        $this->supportsAttribute($number)->shouldReturn(true);

        $boolean->getType()->willReturn('pim_catalog_boolean');
        $this->supportsAttribute($boolean)->shouldReturn(true);

        $date->getType()->willReturn('pim_catalog_date');
        $this->supportsAttribute($date)->shouldReturn(true);

        $other->getType()->willReturn(Argument::any());
        $this->supportsAttribute($other)->shouldReturn(false);
    }

    function it_adds_an_attribute_sorter_to_the_query($qb, AttributeInterface $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('text');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $qb->expr()->willReturn(new Expr());
        $qb->getRootAlias()->willReturn('p');

        $qb->getDQLPart('join')->willReturn([]);
        $qb->resetDQLPart('join')->shouldBeCalled();

        $condition = "sorterVsku.attribute = 42";
        $qb->leftJoin('p.values', 'sorterVsku', 'WITH', $condition)->shouldBeCalled();
        $qb->addOrderBy('sorterVsku.text', 'DESC')->shouldBeCalled();

        $qb->getRootAlias()->willReturn('p');
        $qb->addOrderBy("p.id")->shouldBeCalled();

        $this->addAttributeSorter($sku, 'DESC');
    }

    function it_adds_a_field_sorter_to_the_query($qb)
    {
        $qb->getRootAliases()->willReturn(['r']);
        $qb->getRootAlias()->willReturn('r');

        $qb->addOrderBy('r.my_field', 'DESC')->shouldBeCalled();
        $qb->addOrderBy('r.id')->shouldBeCalled();

        $this->addFieldSorter('my_field', 'DESC');
    }
}
