<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

final class CleanLineBreaksInTextAttributesSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_cleans_text_atributes_and_returns_cleaned_fields(GetAttributes $getAttributes)
    {
        $item = ['values' => [
            'title' => [
                ['data' => 'ok'],
                ['data' => "line\nbreak"],
            ],
            'subtitle' => [
                ['data' => "another line\nbreak with |pipe|"],
            ],
            'description' => [
                ['data' => 'ok'],
                ['data' => "line\r\nbreak"],
            ],
        ]];

        $getAttributes->forCodes(['title', 'subtitle', 'description'])->willReturn([
            'title' => $this->buildAttribute('title', AttributeTypes::TEXT),
            'subtitle' => $this->buildAttribute('subtitle', AttributeTypes::TEXT),
            'description' => $this->buildAttribute('description', AttributeTypes::TEXTAREA),
        ]);

        $this->cleanStandardFormat($item)->shouldReturn(['values' => [
            'title' => [
                ['data' => 'ok'],
                ['data' => 'line break'],
            ],
            'subtitle' => [
                ['data' => 'another line break with |pipe|'],
            ],
            'description' => [
                ['data' => 'ok'],
                ['data' => "line\r\nbreak"],
            ],
        ]]);
    }

    function it_cannot_clean_if_field_is_unknown(GetAttributes $getAttributes)
    {
        $item = ['values' => [
            'unknown' => [
                ['data' => 'ok'],
                ['data' => "line\nbreak"],
            ],
            'title' => [
                ['data' => 'ok'],
                ['data' => "line\nbreak1"],
                ['data' => "line\nbreak2"],
            ],
        ]];

        $getAttributes->forCodes(['unknown', 'title'])->willReturn([
            'title' => $this->buildAttribute('title', AttributeTypes::TEXT),
        ]);

        $this->cleanStandardFormat($item)->shouldReturn(['values' => [
            'unknown' => [
                ['data' => 'ok'],
                ['data' => "line\nbreak"],
            ],
            'title' => [
                ['data' => 'ok'],
                ['data' => 'line break1'],
                ['data' => 'line break2'],
            ],
        ]]);
    }

    function it_cleans_several_sort_of_line_breaks(GetAttributes $getAttributes)
    {
        $item = ['values' => [
            'title' => [
                ['data' => 'ok'],
                ['data' => "line\r\nbreak1"],
                ['data' => "line\rbreak2"],
                ['data' => "line\nbreak3"],
                ['data' => "line\n\n\nbreak4"],
            ],
        ]];

        $getAttributes->forCodes(['title'])->willReturn([
            'title' => $this->buildAttribute('title', AttributeTypes::TEXT),
        ]);

        $this->cleanStandardFormat($item)->shouldReturn(['values' => [
            'title' => [
                ['data' => 'ok'],
                ['data' => 'line break1'],
                ['data' => 'line break2'],
                ['data' => 'line break3'],
                ['data' => 'line   break4'],
            ],
        ]]);
    }

    private function buildAttribute(string $code, string $type): Attribute
    {
        return new Attribute(
            $code,
            $type,
            [],
            true,
            true,
            null,
            null,
            null,
            '',
            []
        );
    }
}
