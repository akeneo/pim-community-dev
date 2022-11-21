<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetSimpleReferenceEntityValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('attribute_name', null, null, 'Akeneo');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SetSimpleReferenceEntityValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    public function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('attribute_name');
    }

    public function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn(null);
    }

    public function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn(null);
    }

    public function it_returns_the_record_code()
    {
        $this->recordCode()->shouldReturn('Akeneo');
    }

    public function it_cannot_be_instantiated_with_an_empty_record_code()
    {
        $this->beConstructedWith('attribute_name', null, null, '');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_instantiated_with_0_as_record_code()
    {
        $this->beConstructedWith('attribute_name', null, null, '0');
        $this->shouldNotThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
