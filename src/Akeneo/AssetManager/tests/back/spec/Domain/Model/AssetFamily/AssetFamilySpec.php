<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class AssetFamilySpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = AssetFamilyIdentifier::fromString('designer');
        $labelCollection = [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur'
        ];
        $this->beConstructedThrough(
            'create',
            [
                $identifier,
                $labelCollection,
                Image::createEmpty(),
                RuleTemplateCollection::empty(),
                new NullNamingConvention()
            ]
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AssetFamily::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = AssetFamilyIdentifier::fromString('designer');
        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_is_comparable_to_another_asset_family()
    {
        $sameIdentifier = AssetFamilyIdentifier::fromString('designer');
        $sameAssetFamily = AssetFamily::create(
            $sameIdentifier,
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->equals($sameAssetFamily)->shouldReturn(true);

        $anotherIdentifier = AssetFamilyIdentifier::fromString('same_identifier');
        $sameAssetFamily = AssetFamily::create(
            $anotherIdentifier,
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->equals($sameAssetFamily)->shouldReturn(false);
    }

    public function it_returns_the_translated_label() {
        $this->getLabel('fr_FR')->shouldReturn('Concepteur');
        $this->getLabel('en_US')->shouldReturn('Designer');
        $this->getLabel('ru_RU')->shouldReturn(null);
    }

    public function it_returns_the_locale_code_from_which_the_asset_family_is_translated($labelCollection) {
        $this->getLabelCodes()->shouldReturn(['en_US', 'fr_FR']);
    }

    public function it_updates_labels()
    {
        $labelCollection = LabelCollection::fromArray(['fr_FR' => 'Concepteur']);

        $this->updateLabels($labelCollection);
        $this->getLabel('fr_FR')->shouldBe('Concepteur');
    }

    public function it_merges_labels_on_update()
    {
        $labelCollection = LabelCollection::fromArray(['de_DE' => 'New label']);
        $this->updateLabels($labelCollection);

        $this->getLabelCodes()->shouldReturn(['en_US', 'fr_FR', 'de_DE']);
        $this->getLabel('de_DE')->shouldBe('New label');
    }

    public function it_updates_image(Image $image)
    {
        $this->updateImage($image);
        $this->getImage()->shouldBe($image);
    }

    public function it_updates_transformation_collection(TransformationCollection $transformations)
    {
        $assetFamily = $this->withTransformationCollection($transformations);
        $assetFamily->getTransformationCollection()->shouldReturn($transformations);
    }
}
