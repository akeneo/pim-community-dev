<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdentifierSpec extends ObjectBehavior
{
    function it_is_a_product_identifier()
    {
        $this->beConstructedWith('AKN-123');
        $this->shouldHaveType(ProductIdentifier::class);
    }

    function it_generates_simple_prefixes()
    {
        $this->beConstructedWith('AKN-123');
        $this->getPrefixes()->shouldReturn([
            'AKN-' => 123,
            'AKN-1' => 23,
            'AKN-12' => 3,
        ]);
    }

    function it_generates_complex_prefixes()
    {
        $this->beConstructedWith('AKN-123-foo-456');
        $this->getPrefixes()->shouldReturn([
            'AKN-' => 123,
            'AKN-1' => 23,
            'AKN-12' => 3,
            'AKN-123-foo-' => 456,
            'AKN-123-foo-4' => 56,
            'AKN-123-foo-45' => 6,
        ]);
    }

    function it_does_not_return_any_prefix()
    {
        $this->beConstructedWith('AKN-foo');
        $this->getPrefixes()->shouldReturn([]);
    }

    function it_does_not_generate_prefixes_with_too_big_numbers()
    {
        // 9223372036854775807123
        $this->beConstructedWith(\sprintf('%d123', PHP_INT_MAX));
        $this->getPrefixes()->shouldReturn([
            // These next 3 lines will not appear as numbers are bigger than PHP_INT_MAX.
            // '' => 9223372036854775807123,
            // '9' => 223372036854775807123,
            // '92' => 23372036854775807123,
            '922' => 3372036854775807123,
            '9223' => 372036854775807123,
            '92233' => 72036854775807123,
            '922337' => 2036854775807123,
            '9223372' => 36854775807123,
            '92233720' => 36854775807123,
            '922337203' => 6854775807123,
            '9223372036' => 854775807123,
            '92233720368' => 54775807123,
            '922337203685' => 4775807123,
            '9223372036854' => 775807123,
            '92233720368547' => 75807123,
            '922337203685477' => 5807123,
            '9223372036854775' => 807123,
            '92233720368547758' => 7123,
            '922337203685477580' => 7123,
            '9223372036854775807' => 123,
            '92233720368547758071' => 23,
            '922337203685477580712' => 3,
        ]);
    }
}
