<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints\AssetShouldExist;
use Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints\AssetsShouldBelongToAssetFamily;
use Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints\ThereShouldBeLessAssetsInValueThanLimit;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class AssetMultipleLinkGuesserSpec extends ObjectBehavior
{
    public function it_supports_the_asset_multiple_link_attribute(
        AttributeInterface $booleanAttribute,
        AttributeInterface $assetMultipleLinkAttribute
    ) {
        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $assetMultipleLinkAttribute->getType()->willReturn(AttributeTypes::ASSET_COLLECTION);

        $this->supportAttribute($booleanAttribute)->shouldReturn(false);
        $this->supportAttribute($assetMultipleLinkAttribute)->shouldReturn(true);
    }

    public function it_guesses_the_constraints_for_the_attribute(
        AttributeInterface $attribute,
        ThereShouldBeLessAssetsInValueThanLimit $constraint
    ) {
        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldBeArray();
        $constraints[0]->shouldBeAnInstanceOf(AssetsShouldBelongToAssetFamily::class);
        $constraints[1]->shouldBeAnInstanceOf(ThereShouldBeLessAssetsInValueThanLimit::class);
    }
}
