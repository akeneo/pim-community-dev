<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMetricValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
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
        $valueUserIntents = [new SetTextValue('name', null, null, 'foo')];
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
            $valueUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
    }

    function it_can_be_constructed_with_a_set_number_value_intent()
    {
        $valueUserIntents = [new SetNumberValue('name', null, null, 10)];
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
            $valueUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
    }

    function it_can_be_constructed_with_a_set_metric_value_intent()
    {
        $valueUserIntents = [new SetMetricValue('power', null, null, '100', 'KILOWATT')];
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
            $valueUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
    }

    function it_can_be_constructed_with_a_set_textarea_value_intent()
    {
        $valueUserIntents = [new SetTextareaValue('name', null, null, "<p><span style=\"font-weight: bold;\">title</span></p><p>text</p>")];
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
            $valueUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
    }

    function it_can_be_constructed_with_a_clear_value_intent()
    {
        $valueUserIntents = [new ClearValue('name', null, null)];
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
            $valueUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
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

    function it_can_be_constructed_with_a_set_boolean_value_intent()
    {
        $valueUserIntents = [new SetBooleanValue('name', null, null, true)];
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
            $valueUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
    }

    function it_can_be_constructed_with_a_set_date_value_intent()
    {
        $valueUserIntents = [new SetDateValue('name', null, null, new \DateTime("2022-03-04T09:35:24+00:00"))];
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
            $valueUserIntents
        );
        $this->userId()->shouldReturn(1);
        $this->productIdentifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
    }
}
