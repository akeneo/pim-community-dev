<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Api\Command;

use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertProductCommandSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            1,
            'identifier1',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            []
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpsertProductCommand::class);
    }

    function it_can_be_constructed_with_a_set_text_value_intent()
    {
        $valuesUserIntents = [new SetTextValue('name', null, null, 'foo')];
        $this->beConstructedWith(
            1,
            'identifier1',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $valuesUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valuesUserIntent()->shouldReturn($valuesUserIntents);
    }

    function it_cannot_be_constructed_with_bad_value_user_intent()
    {
        $this->beConstructedWith(
            1,
            '',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            [new \stdClass]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
