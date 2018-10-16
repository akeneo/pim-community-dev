<?php

namespace Specification\Akeneo\Asset\Bundle\Versioning\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\VariationInterface;

class VariationUpdateGuesserSpec extends ObjectBehavior
{
    function it_support_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('an_other_action')->shouldReturn(false);
    }

    function it_add_asset_to_pending_on_variation_update(
        EntityManager $em,
        VariationInterface $variation,
        AssetInterface $asset
    ) {
        $variation->getAsset()->willReturn($asset);
        $this->guessUpdates($em, $variation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$asset]);
    }

    function it_does_not_add_asset_to_pending_on_other_entity_update(EntityManager $em)
    {
        $this->guessUpdates($em, new \StdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([]);
    }
}
