<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryGetExistingAttributeOptionsWithValues;
use PhpSpec\ObjectBehavior;

class InMemoryGetExistingAttributeOptionsWithValuesSpec extends ObjectBehavior
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
        $red->addOptionValue($this->buildAttributeOptionValue('en_US', 'red'));
        $red->addOptionValue($this->buildAttributeOptionValue('fr_FR', 'rouge'));
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
        $this->shouldHaveType(InMemoryGetExistingAttributeOptionsWithValues::class);
    }

    function it_is_a_get_existing_attribute_options_with_values_query()
    {
        $this->shouldImplement(GetExistingAttributeOptionsWithValues::class);
    }

    function it_returns_attribute_options_with_values()
    {
        $this->fromAttributeCodeAndOptionCodes(['color.red', 'color.green', 'color.blue'])
            ->shouldReturn([
                'color.red' => ['en_US' => 'red', 'fr_FR' => 'rouge'],
                'color.green' => [],
            ]);
    }

    function it_returns_empty_array_for_unknown_options()
    {
        $this->fromAttributeCodeAndOptionCodes(['unknown.red', 'unknown.green', 'color.unknown'])
            ->shouldReturn([]);
    }

    function buildAttributeOptionValue(string $locale, string $value): AttributeOptionValue
    {
        $attributeOptionValue = new AttributeOptionValue();
        $attributeOptionValue->setLocale($locale);
        $attributeOptionValue->setValue($value);

        return $attributeOptionValue;
    }
}
