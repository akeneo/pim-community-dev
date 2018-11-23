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

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorRecordsByIdentifiers;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorRecordsByIdentifiersTest extends TestCase
{
    /** @var InMemoryFindConnectorRecordsByIdentifiers */
    private $findConnectorRecordsByIdentifiers;

    public function setUp()
    {
        $this->findConnectorRecordsByIdentifiers = new InMemoryFindConnectorRecordsByIdentifiers();
    }

    /**
     * @test
     */
    public function it_finds_connector_records_for_a_given_list_of_identifiers()
    {
        $kartellRecord = new ConnectorRecord(
            RecordCode::fromString('kartell'),
            LabelCollection::fromArray(['en_US' => 'Kartell']),
            Image::createEmpty(),
            []
        );
        $kartellRecordIdentifier = RecordIdentifier::fromString('brand_kartell_fingerprint');
        $this->findConnectorRecordsByIdentifiers->save($kartellRecordIdentifier, $kartellRecord);

        $lexonRecord = new ConnectorRecord(
            RecordCode::fromString('lexon'),
            LabelCollection::fromArray(['en_US' => 'Lexon']),
            Image::createEmpty(),
            []
        );
        $lexonRecordIdentifier = RecordIdentifier::fromString('brand_lexon_fingerprint');
        $this->findConnectorRecordsByIdentifiers->save($lexonRecordIdentifier, $lexonRecord);

        $alessiRecord = new ConnectorRecord(
            RecordCode::fromString('alessi'),
            LabelCollection::fromArray(['en_US' => 'Alessi']),
            Image::createEmpty(),
            []
        );
        $alessiRecordIdentifier = RecordIdentifier::fromString('brand_alessi_fingerprint');
        $this->findConnectorRecordsByIdentifiers->save($alessiRecordIdentifier, $alessiRecord);

        $recordsFound = ($this->findConnectorRecordsByIdentifiers)([
            $lexonRecordIdentifier->normalize(),
            $alessiRecordIdentifier->normalize(),
            'brand_muuto_fingerprint',
        ], RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            null
        ));

        $this->assertEquals([$lexonRecord, $alessiRecord], $recordsFound);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_records_are_found()
    {
        $connectorRecord = new ConnectorRecord(
            RecordCode::fromString('kartell'),
            LabelCollection::fromArray(['en_US' => 'Kartell']),
            Image::createEmpty(),
            []
        );
        $recordIdentifier = RecordIdentifier::fromString('brand_kartell_fingerprint');
        $this->findConnectorRecordsByIdentifiers->save($recordIdentifier, $connectorRecord);

        $recordsFound = ($this->findConnectorRecordsByIdentifiers)([
            'brand_lexon_fingerprint',
            'brand_muuto_fingerprint',
        ], RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            null
        ));

        $this->assertEquals([], $recordsFound);
    }

    /**
     * @test
     */
    public function it_finds_connector_records_and_filters_the_values_by_channel()
    {
        $connectorRecord = new ConnectorRecord(
            RecordCode::fromString('kartell'),
            LabelCollection::fromArray([
                'en_US' => 'Kartell english label',
                'fr_FR' => 'Kartell french label',
            ]),
            Image::createEmpty(),
            [
                'name'  => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'US ecommerce name',
                    ],
                    [
                        'locale'  => 'en_US',
                        'channel' => 'print',
                        'data'    => 'US print name',
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => 'ecommerce',
                        'data'    => 'FR ecommerce name',
                    ]
                ],
                'description' => [
                    [
                        'locale'  => null,
                        'channel' => 'print',
                        'data'    => 'Description for print channel',
                    ],
                ]
            ]
        );
        $recordIdentifier = RecordIdentifier::fromString('brand_kartell_fingerprint');

        $this->findConnectorRecordsByIdentifiers->save($recordIdentifier, $connectorRecord);

        $expectedConnectorRecord = new ConnectorRecord(
            RecordCode::fromString('kartell'),
            LabelCollection::fromArray([
                'en_US' => 'Kartell english label',
                'fr_FR' => 'Kartell french label',
            ]),
            Image::createEmpty(),
            [
                'name'  => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'US ecommerce name',
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => 'ecommerce',
                        'data'    => 'FR ecommerce name',
                    ]
                ],
            ]
        );

        $recordsFound = ($this->findConnectorRecordsByIdentifiers)([
            $recordIdentifier->normalize(),
        ], RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleIdentifierCollection::empty(),
            10,
            null
        ));

        $this->assertEquals([$expectedConnectorRecord], $recordsFound);
    }

    /**
     * @test
     */
    public function it_finds_connector_records_and_filters_the_values_by_locale()
    {
        $connectorRecord = new ConnectorRecord(
            RecordCode::fromString('kartell'),
            LabelCollection::fromArray([
                'en_US' => 'Kartell english label',
                'fr_FR' => 'Kartell french label',
            ]),
            Image::createEmpty(),
            [
                'description' => [
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'en_US',
                        'data'      => 'English description.',
                    ],
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'fr_FR',
                        'data'      => 'French description.',
                    ],
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'de_DE',
                        'data'      => 'German description.',
                    ],
                ],
                'short_description' => [
                    [
                        'channel'   => 'tablet',
                        'locale'    => 'fr_FR',
                        'data'      => 'French short description.',
                    ],
                ],
                'not_localizable_value' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => null,
                        'data'    => 'Not localizable value.'
                    ]
                ],
            ]
        );
        $recordIdentifier = RecordIdentifier::fromString('brand_kartell_fingerprint');

        $this->findConnectorRecordsByIdentifiers->save($recordIdentifier, $connectorRecord);

        $expectedConnectorRecord = new ConnectorRecord(
            RecordCode::fromString('kartell'),
            LabelCollection::fromArray([
                'en_US' => 'Kartell english label',
            ]),
            Image::createEmpty(),
            [
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'English description.',
                    ],
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'de_DE',
                        'data'      => 'German description.',
                    ],
                ],
                'not_localizable_value' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => null,
                        'data'    => 'Not localizable value.'
                    ],
                ],
            ]
        );

        $recordsFound = ($this->findConnectorRecordsByIdentifiers)([
            $recordIdentifier->normalize(),
        ], RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleIdentifierCollection::fromNormalized([
                'en_US',
                'de_DE',
            ]),
            10,
            null
        ));

        $this->assertEquals([$expectedConnectorRecord], $recordsFound);
    }
}
