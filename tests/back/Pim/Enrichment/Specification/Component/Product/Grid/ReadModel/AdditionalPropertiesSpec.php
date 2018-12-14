<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use PhpSpec\ObjectBehavior;

class AdditionalPropertiesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            new AdditionalProperty('name_1', 'value_1'),
            new AdditionalProperty('name_2', 'value_2')
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AdditionalProperties::class);
    }

    function it_iterates_over_additional_properties()
    {
        $this->getIterator()->shouldBeLike(
            new \ArrayIterator([
                new AdditionalProperty('name_1', 'value_1'),
                new AdditionalProperty('name_2', 'value_2')
            ])
        );
    }

    function it_adds_an_additional_properties()
    {
        $this->addAdditionalProperty(new AdditionalProperty('name_3', 'value_3'))
            ->shouldBeLike(
                new AdditionalProperties([
                    new AdditionalProperty('name_1', 'value_1'),
                    new AdditionalProperty('name_2', 'value_2'),
                    new AdditionalProperty('name_3', 'value_3')
                ])
            );
    }
}
