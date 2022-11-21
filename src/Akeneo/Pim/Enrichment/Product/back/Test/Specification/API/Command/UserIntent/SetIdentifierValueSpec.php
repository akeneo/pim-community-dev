<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

class SetIdentifierValueSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('sku', 'my_beautiful_product');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SetIdentifierValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    public function it_exposes_the_attribute_code(): void
    {
        $this->attributeCode()->shouldReturn('sku');
    }

    public function it_has_a_null_locale(): void
    {
        $this->localeCode()->shouldBe(null);
    }

    public function it_has_a_null_channel(): void
    {
        $this->channelCode()->shouldBe(null);
    }

    public function it_exposes_its_value(): void
    {
        $this->value()->shouldReturn('my_beautiful_product');
    }
}
