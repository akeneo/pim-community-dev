<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetFamilySpec extends ObjectBehavior
{
    function it_can_be_constructed_with_family_code()
    {
        $this->beConstructedWith('accessories');
        $this->shouldBeAnInstanceOf(SetFamily::class);
        $this->familyCode()->shouldReturn('accessories');
    }
}
