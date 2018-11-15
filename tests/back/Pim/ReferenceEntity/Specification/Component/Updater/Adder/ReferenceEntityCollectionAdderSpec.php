<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\ReferenceEntity\Component\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AbstractAttributeAdder;
use Akeneo\Pim\ReferenceEntity\Component\Updater\Adder\ReferenceEntityCollectionAdder;
use Akeneo\Pim\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
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

        $starckIdentifier = RecordIdentifier::create(
            'designer',
            'starck',
            'fingerprint'
        );
        $originalData = [
            Record::create(
                $starckIdentifier,
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('starck'),
                ['fr_Fr' => 'Philippe Starck'],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            ),
        ];
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
