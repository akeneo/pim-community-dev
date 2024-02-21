<?php

namespace spec\Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryGetExistingAttributeOptionCodes;
use PhpSpec\ObjectBehavior;

class InMemoryGetExistingAttributeOptionCodesSpec extends ObjectBehavior
{
    function let()
    {
        $attributeOptionRepository = new InMemoryAttributeOptionRepository();

        $color = new Attribute();
        $color->setCode('color');
        $color->setType('pim_catalog_simpleselect');
        $red = new AttributeOption();
        $red->setAttribute($color);
        $red->setCode('red');
        $attributeOptionRepository->save($red);
        $green = new AttributeOption();
        $green->setAttribute($color);
        $green->setCode('green');
        $attributeOptionRepository->save($green);

        $sizes = new Attribute();
        $sizes->setCode('sizes');
        $sizes->setType('pim_catalog_simpleselect');
        $s = new AttributeOption();
        $s->setCode('s');
        $s->setAttribute($sizes);
        $attributeOptionRepository->save($s);
        $m = new AttributeOption();
        $m->setCode('m');
        $m->setAttribute($sizes);
        $attributeOptionRepository->save($m);

        $this->beConstructedWith($attributeOptionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetExistingAttributeOptionCodes::class);
    }

    function it_is_a_get_existing_attribute_option_codes_query()
    {
        $this->shouldImplement(GetExistingAttributeOptionCodes::class);
    }

    function it_returns_nothing_if_no_options_are_passed()
    {
        $this->fromOptionCodesByAttributeCode([])->shouldReturn([]);
    }

    function it_filters_non_existing_option_codes()
    {
        $this->fromOptionCodesByAttributeCode(
            [
                'color' => ['red', 'blue', 'green'],
                'sizes' => ['m', 'l', 'xl'],
            ]
        )->shouldReturn(
            [
                'color' => ['red', 'green'],
                'sizes' => ['m'],
            ]
        );
    }

    function it_filters_non_existing_attribute_codes()
    {
        $this->fromOptionCodesByAttributeCode(
            [
                'collections' => ['summer_2019'],
                'color' => ['green'],
            ]
        )->shouldReturn(
            [
                'color' => ['green'],
            ]
        );
    }
}
