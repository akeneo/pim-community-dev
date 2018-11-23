<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AbstractAttributeAdder;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Adder\ReferenceEntityCollectionAdder;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionAdderSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $supportedTypes = ['akeneo_reference_entity_collection'];

        $this->beConstructedWith($entityWithValuesBuilder, $supportedTypes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionAdder::class);
        $this->shouldHaveType(AbstractAttributeAdder::class);
    }

    function it_adds_attribute_data(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        ReferenceEntityCollectionValue $referenceEntityCollectionValue
    ) {
        $options = [
            'locale' => null,
            'scope' => null,
        ];

        $originalData = [RecordCode::fromString('starck')];
        $newData = ['dyson', 'arad'];

        $attribute->getCode()->willReturn('my_attribute_code');

        $entityWithValues->getValue('my_attribute_code', null, null)->willReturn($referenceEntityCollectionValue);
        $referenceEntityCollectionValue->getData()->willReturn($originalData);

        $entityWithValuesBuilder->addOrReplaceValue(
            $entityWithValues,
            $attribute,
            $options['locale'],
            $options['scope'],
            ['dyson', 'arad', 'starck']
        )->shouldBeCalled();

        $this->addAttributeData($entityWithValues, $attribute, $newData, $options);
    }
}
