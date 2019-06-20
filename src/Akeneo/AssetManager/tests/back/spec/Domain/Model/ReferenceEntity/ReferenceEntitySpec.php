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

namespace spec\Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class ReferenceEntitySpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = ReferenceEntityIdentifier::fromString('designer');
        $labelCollection = [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur'
        ];
        $this->beConstructedThrough('create', [$identifier, $labelCollection, Image::createEmpty()]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntity::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = ReferenceEntityIdentifier::fromString('designer');
        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_is_comparable_to_another_reference_entity()
    {
        $sameIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $sameReferenceEntity = ReferenceEntity::create(
            $sameIdentifier,
            [],
            Image::createEmpty()
        );
        $this->equals($sameReferenceEntity)->shouldReturn(true);

        $anotherIdentifier = ReferenceEntityIdentifier::fromString('same_identifier');
        $sameReferenceEntity = ReferenceEntity::create(
            $anotherIdentifier,
            [],
            Image::createEmpty()
        );
        $this->equals($sameReferenceEntity)->shouldReturn(false);
    }

    public function it_returns_the_translated_label() {
        $this->getLabel('fr_FR')->shouldReturn('Concepteur');
        $this->getLabel('en_US')->shouldReturn('Designer');
        $this->getLabel('ru_RU')->shouldReturn(null);
    }

    public function it_returns_the_locale_code_from_which_the_reference_entity_is_translated($labelCollection) {
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
}
