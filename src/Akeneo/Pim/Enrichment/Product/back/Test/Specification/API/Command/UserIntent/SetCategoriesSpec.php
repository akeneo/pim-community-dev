<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetCategoriesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['categoryA', 'categoryB']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetCategories::class);
        $this->shouldImplement(CategoryUserIntent::class);

        $this->categoryCodes()->shouldReturn(['categoryA', 'categoryB']);
    }

    function it_requires_non_empty_values_in_array()
    {
        $this->beConstructedWith(['']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_requires_string_values_in_the_array()
    {
        $this->beConstructedWith(['test', 42]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
