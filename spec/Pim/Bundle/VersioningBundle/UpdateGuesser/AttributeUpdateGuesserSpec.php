<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class AttributeUpdateGuesserSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, ProductRepositoryInterface $repository)
    {
        $registry->getRepository('product')->willReturn($repository);

        $this->beConstructedWith($registry, 'product');
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface');
    }

    function it_supports_entity_deletion()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(false);
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)->shouldReturn(false);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_marks_products_as_updated_when_an_attribute_is_removed(
        $repository,
        EntityManager $em,
        AbstractAttribute $attribute,
        ProductInterface $foo,
        ProductInterface $bar
    ) {
        $repository->findAllWithAttribute($attribute)->willReturn([$foo, $bar]);

        $this->guessUpdates($em, $attribute, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$foo, $bar]);
    }
}
