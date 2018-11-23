<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class ReferenceEntityValueSpec extends ObjectBehavior
{
    function it_is_initializable(RecordCode $recordCode)
    {
        $this->beConstructedThrough('value', ['my_reference_entity', $recordCode]);
        $this->shouldHaveType(ReferenceEntityValue::class);
        $this->shouldHaveType(ValueInterface::class);
    }

    function it_gets_a_record_identifier_as_data(RecordCode $recordCode)
    {
        $this->beConstructedThrough('value', ['my_reference_entity', $recordCode]);
        $this->getData()->shouldReturn($recordCode);
    }

    function it_can_be_casted_as_a_string(RecordCode $recordCode)
    {
        $recordCode->__toString()->willReturn('adidas');
        $this->beConstructedThrough('value', ['my_reference_entity', $recordCode]);
        $this->__toString()->shouldReturn('adidas');
    }
}
