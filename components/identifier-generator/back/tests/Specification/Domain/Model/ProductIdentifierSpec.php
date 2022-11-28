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

    function it_generates_psimple_refixes()
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
}
