<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use PhpSpec\ObjectBehavior;

class LabelCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromArray', [['en_US' => 'A US label', 'fr_FR' => 'Un label français']]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LabelCollection::class);
    }

    public function it_cannot_create_a_label_collection_if_keys_are_not_string()
    {
        $this->beConstructedThrough('fromArray', [['label1', 'label2']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_create_a_label_collection_if_values_are_an_integer()
    {
        $this->beConstructedThrough('fromArray', [['en_US' => 1]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_create_a_label_collection_if_keys_are_empty()
    {
        $this->beConstructedThrough('fromArray', [['' => 'Book']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_is_constructed_from_an_array_of_labels_and_returns_the_translated_label()
    {
        $this->getLabel('en_US')->shouldReturn('A US label');
        $this->getLabel('fr_FR')->shouldReturn('Un label français');
    }

    public function it_returns_null_if_the_locale_is_not_found()
    {
        $this->getLabel('ru_RU')->shouldReturn(null);
    }

    public function it_does_not_add_empty_labels_to_the_collection()
    {
        $this->beConstructedThrough('fromArray',[['en_US' => 'A US label', 'fr_FR' => '']]);

        $this->getLabel('en_US')->shouldReturn('A US label');
        $this->getLabel('fr_FR')->shouldReturn(null);
        $this->normalize()->shouldReturn(['en_US' => 'A US label']);
    }

    public function it_tells_if_it_has_label() {
        $this->hasLabel('en_US')->shouldReturn(true);
        $this->hasLabel('ru_RU')->shouldReturn(false);
    }

    public function it_returns_the_locale_codes_it_has_translation_for()
    {
        $this->getLocaleCodes()->shouldReturn(['en_US', 'fr_FR']);
    }

    public function it_can_normalize_itself()
    {
        $this->normalize()->shouldReturn(['en_US' => 'A US label', 'fr_FR' => 'Un label français']);
    }

    public function it_filters_the_labels_by_locale_identifiers()
    {
        $this->beConstructedThrough('fromArray',[[
            'en_US' => 'A US label',
            'fr_FR' => 'Un label français',
            'de_DE' => 'Eine deutsche label'
        ]]);

        $this->filterByLocaleIdentifiers([
            LocaleIdentifier::fromCode('en_US'),
            LocaleIdentifier::fromCode('de_DE'),
        ])->shouldBeLike(LabelCollection::fromArray([
            'en_US' => 'A US label',
            'de_DE' => 'Eine deutsche label',
        ]));
    }
}
