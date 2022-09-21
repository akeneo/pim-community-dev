<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DelimiterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['-']);
    }

    function it_is_a_delimiter()
    {
        $this->shouldBeAnInstanceOf(Delimiter::class);
    }

    function it_represents_a_delimiter()
    {
        $this->asString()->shouldReturn('-');
    }
}
