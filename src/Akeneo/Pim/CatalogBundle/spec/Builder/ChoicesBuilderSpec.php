<?php

namespace spec\Pim\Bundle\CatalogBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\Model\ChosableInterface;

class ChoicesBuilderSpec extends ObjectBehavior
{
    function it_builds_a_list_of_choices(
        ChosableInterface $item1,
        ChosableInterface $item2,
        ChosableInterface $item3
    ) {
        $item1->getChoiceValue()->willReturn('val_1');
        $item1->getChoiceLabel()->willReturn('Choice 1');
        $item2->getChoiceValue()->willReturn('val_2');
        $item2->getChoiceLabel()->willReturn('Choice 2');
        $item3->getChoiceValue()->willReturn('val_3');
        $item3->getChoiceLabel()->willReturn('Choice 3');

        $this->buildChoices([$item1, $item2, $item3])->shouldReturn(
            [
                'Choice 1' => 'val_1',
                'Choice 2' => 'val_2',
                'Choice 3' => 'val_3',
            ]
        );
    }

    function it_throws_an_exception_when_handling_invalid_items(ChosableInterface $validItem)
    {
        $validItem->getChoiceValue()->willReturn('val_1');
        $validItem->getChoiceLabel()->willReturn('Choice 1');
        $invalidItem = new \stdClass();

        $this->shouldThrow(
            new \InvalidArgumentException(sprintf(
                '%s must implement Pim\Component\Enrich\Model\ChosableInterface',
                get_class($invalidItem)
            ))
        )->duringBuildChoices([$validItem, $invalidItem]);
    }
}
