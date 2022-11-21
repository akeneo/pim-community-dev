<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\GroupUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroupsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['promotions', 'toto']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AddToGroups::class);
        $this->shouldImplement(GroupUserIntent::class);
    }

    public function it_returns_the_group_codes()
    {
        $this->groupCodes()->shouldReturn(['promotions', 'toto']);
    }

    public function it_throws_an_error_if_parameter_is_a_code_is_an_empty_string()
    {
        $this->beConstructedWith(['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_error_if_parameter_is_an_empty_array()
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
