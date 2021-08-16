<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure\FindAttributeOptionLabels;
use PhpSpec\ObjectBehavior;

class FindAttributeOptionLabelsSpec extends ObjectBehavior
{
    public function let(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ): void {
        $this->beConstructedWith($getExistingAttributeOptionsWithValues);
    }

    public function it_is_initializable(): void
    {
        $this->beAnInstanceOf(FindAttributeOptionLabels::class);
    }

    public function it_finds_the_labels_of_an_attribute_options(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ): void {
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            ['color.red', 'color.blue']
        )->willReturn(
            ['color.red' => ['fr_FR' => 'Rouge'], 'color.blue' => ['fr_FR' => 'Bleu']]
        );

        $this->byAttributeCodeAndOptionCodes('color', ['red', 'blue'], 'fr_FR')->shouldReturn(
            ['red' => 'Rouge', 'blue' => 'Bleu']
        );
    }

    public function it_returns_an_empty_list_if_no_label_for_any_option(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ): void {
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            ['color.red', 'color.blue']
        )->willReturn(
            ['color.red' => ['fr_FR' => null], 'color.blue' => ['fr_FR' => null]]
        );

        $this->byAttributeCodeAndOptionCodes('color', ['red', 'blue'], 'fr_FR')->shouldReturn(
            ['red' => null, 'blue' => null]
        );
    }
}
