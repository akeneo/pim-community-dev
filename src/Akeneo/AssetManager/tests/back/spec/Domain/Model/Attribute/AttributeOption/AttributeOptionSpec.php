<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class AttributeOptionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough(
            'create',
            [
                OptionCode::fromString('red'),
                LabelCollection::fromArray(['fr_FR' => 'Rouge', 'en_US' => 'Red'])
            ]
        ) ;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOption::class);
    }

    public function it_updates_its_label()
    {
        $this->updateLabels(LabelCollection::fromArray(['de_DE' => 'Rood' ]));
        $this->normalize()->shouldReturn([
            'code' => 'red',
            'labels' => [
                'fr_FR' => 'Rouge',
                'en_US' => 'Red',
                'de_DE' => 'Rood'
            ]
        ]);
    }

    public function it_can_normalize_itself()
    {
        $this->normalize()->shouldReturn([
            'code' => 'red',
            'labels' => [
                'fr_FR' => 'Rouge',
                'en_US' => 'Red'
            ]
        ]);
    }

    public function it_returns_its_code()
    {
        $this->getCode()->__toString()->shouldReturn('red');
    }
}
