<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Category\Api\Command\UserIntents\ValueImageUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetImageValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'name',
            'ecommerce',
            'en_US',
            [
                'size' => 168107,
                'extension' => 'jpg',
                'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                'mime_type' => 'image/jpeg',
                'original_filename' => 'shoes.jpg'
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetImageValue::class);
        $this->shouldImplement(ValueImageUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('name');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_value()
    {
        $this->value()->shouldReturn([
            'size' => 168107,
            'extension' => 'jpg',
            'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'shoes.jpg'
        ]);
    }
}
