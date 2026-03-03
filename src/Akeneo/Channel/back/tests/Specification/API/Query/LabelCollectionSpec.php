<?php

namespace Specification\Akeneo\Channel\API\Query;

use Akeneo\Channel\API\Query\LabelCollection;
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

    public function it_is_constructed_from_an_array_of_labels_and_returns_the_translated_label()
    {
        $this->getLabel('en_US')->shouldReturn('A US label');
        $this->getLabel('fr_FR')->shouldReturn('Un label français');
    }

    public function it_returns_null_if_the_locale_is_not_found()
    {
        $this->getLabel('ru_RU')->shouldReturn(null);
    }

    public function it_tells_if_it_has_label()
    {
        $this->hasLabel('en_US')->shouldReturn(true);
        $this->hasLabel('ru_RU')->shouldReturn(false);
    }
}
