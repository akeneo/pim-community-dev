<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Prophecy\Argument;

class VariantGroupUpdateGuesserSpec extends ObjectBehavior
{
    function let(SmartManagerRegistry $registry)
    {
        $this->beConstructedWith($registry, 'GroupInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\UpdateGuesser\VariantGroupUpdateGuesser');
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface');
    }

    function it_supports_entity_updates()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(false);
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)->shouldReturn(false);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_marks_a_variant_group_as_updated_when_its_attributes_are_removed_or_updated(
        $registry,
        EntityManager $em,
        ProductTemplateInterface $productTemplate,
        GroupInterface $group,
        GroupRepositoryInterface $groupRepo
    ) {
        $productTemplate->getId()->willReturn(956);
        $registry->getRepository(Argument::type('string'))->willReturn($groupRepo);
        $groupRepo->getVariantGroupByProductTemplate($productTemplate)->willReturn($group);

        $this->guessUpdates($em, $productTemplate, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$group]);
    }
}
