<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetTextareaValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', "<p><span style=\"font-weight: bold;\">title</span></p><p>text</p>");
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SetTextareaValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    public function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('name');
    }

    public function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    public function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    public function it_returns_the_value()
    {
        $this->value()->shouldReturn("<p><span style=\"font-weight: bold;\">title</span></p><p>text</p>");
    }
}
