<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class ContainsProductsUpdateGuesserSpec extends ObjectBehavior
{
    function it_is_an_update_guesser()
    {
        $this->shouldImplement(UpdateGuesserInterface::class);
    }

    function it_supports_entity_updates_and_deletion()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)->shouldReturn(false);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_marks_products_as_updated_when_a_group_is_removed_or_updated(
        EntityManager $em,
        ProductInterface $foo,
        ProductInterface $bar,
        GroupInterface $group
    ) {
        $group->getProducts()->willReturn([$foo, $bar]);
        $this->guessUpdates($em, $group, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$foo, $bar]);
        $this->guessUpdates($em, $group, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$foo, $bar]);
    }

    function it_marks_products_as_updated_when_a_category_is_removed(
        EntityManager $em,
        ProductInterface $foo,
        ProductInterface $bar,
        CategoryInterface $category
    ) {
        $category->getProducts()->willReturn([$foo, $bar]);
        $this->guessUpdates($em, $category, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$foo, $bar]);
    }

    function it_does_not_mark_products_as_updated_when_a_category_is_updated(
        EntityManager $em,
        ProductInterface $foo,
        ProductInterface $bar,
        CategoryInterface $category
    ) {
        $category->getProducts()->willReturn([$foo, $bar]);
        $this->guessUpdates($em, $category, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([]);
    }
}
