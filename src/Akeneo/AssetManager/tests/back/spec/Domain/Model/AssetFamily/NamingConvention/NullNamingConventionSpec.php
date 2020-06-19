<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NullNamingConventionSpec extends ObjectBehavior
{
    function it_is_a_naming_convention()
    {
        $this->shouldImplement(NamingConventionInterface::class);
    }

    function it_represents_a_null_naming_convention()
    {
        $this->normalize()->shouldBeLike(new \stdClass);
    }

    function it_is_always_empty()
    {
        $this->isEmpty()->shouldReturn(true);
    }
}
