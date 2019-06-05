<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerInterface;
use PhpSpec\ObjectBehavior;

class RecordCollectionConnectorValueTransformerSpec extends ObjectBehavior
{
    function let(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->beConstructedWith($findCodesByIdentifiers);
    }

    function it_is_a_connector_value_transformer()
    {
        $this->shouldImplement(ConnectorValueTransformerInterface::class);
    }

    function it_only_supports_a_value_of_an_record_attribute(
        TextAttribute $textAttribute,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $this->supports($recordCollectionAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_transforms_a_normalized_value_to_a_normalized_connector_value(
        $findCodesByIdentifiers,
        RecordCollectionAttribute $attribute,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ) {
        $attribute->getRecordType()->willReturn($referenceEntityIdentifier);
        $findCodesByIdentifiers
            ->find(['kartell', 'lexon', 'cogip'])
            ->willReturn(['cogip', 'kartell', 'lexon']);

        $this->transform([
            'data'      => ['kartell', 'lexon', 'cogip'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'brands_designer_fingerprint',
        ], $attribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => ['cogip', 'kartell', 'lexon'],
        ]);
    }

    function it_removes_records_that_do_not_exist_in_a_value_containing_records(
        $findCodesByIdentifiers,
        RecordCollectionAttribute $attribute,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ) {
        $attribute->getRecordType()->willReturn($referenceEntityIdentifier);
        $findCodesByIdentifiers
            ->find(['kartell', 'lexon', 'cogip'])
            ->willReturn(['lexon']);

        $this->transform([
            'data'      => ['kartell', 'lexon', 'cogip'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'brands_designer_fingerprint',
        ], $attribute)->shouldReturn([
            'locale'  => 'en_us',
            'channel' => 'ecommerce',
            'data'    => ['lexon'],
        ]);
    }

    function it_returns_null_if_no_records_exist(
        $findCodesByIdentifiers,
        RecordCollectionAttribute $attribute,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ) {
        $attribute->getRecordType()->willReturn($referenceEntityIdentifier);
        $findCodesByIdentifiers->find(['cogip'])->willReturn([]);

        $this->transform([
            'data'      => ['cogip'],
            'locale'    => 'en_us',
            'channel'   => 'ecommerce',
            'attribute' => 'brands_designer_fingerprint',
        ], $attribute)->shouldReturn(null);
    }
}
