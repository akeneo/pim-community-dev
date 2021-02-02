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

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAssetsByIdentifiers;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorAssetsByIdentifiersTest extends TestCase
{
    /** @var InMemoryFindConnectorAssetsByIdentifiers */
    private $findConnectorAssetsByIdentifiers;

    public function setUp(): void
    {
        parent::setUp();

        $this->findConnectorAssetsByIdentifiers = new InMemoryFindConnectorAssetsByIdentifiers();
    }

    /**
     * @test
     */
    public function it_finds_connector_assets_for_a_given_list_of_identifiers()
    {
        $kartellAsset = new ConnectorAsset(
            AssetCode::fromString('kartell'),
            []
        );
        $kartellAssetIdentifier = AssetIdentifier::fromString('brand_kartell_fingerprint');
        $this->findConnectorAssetsByIdentifiers->save($kartellAssetIdentifier, $kartellAsset);

        $lexonAsset = new ConnectorAsset(
            AssetCode::fromString('lexon'),
            []
        );
        $lexonAssetIdentifier = AssetIdentifier::fromString('brand_lexon_fingerprint');
        $this->findConnectorAssetsByIdentifiers->save($lexonAssetIdentifier, $lexonAsset);

        $alessiAsset = new ConnectorAsset(
            AssetCode::fromString('alessi'),
            []
        );
        $alessiAssetIdentifier = AssetIdentifier::fromString('brand_alessi_fingerprint');
        $this->findConnectorAssetsByIdentifiers->save($alessiAssetIdentifier, $alessiAsset);

        $assetsFound = $this->findConnectorAssetsByIdentifiers->find([
            $lexonAssetIdentifier->normalize(),
            $alessiAssetIdentifier->normalize(),
            'brand_muuto_fingerprint',
        ], AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            null,
            []
        ));

        $this->assertEquals([$lexonAsset, $alessiAsset], $assetsFound);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_assets_are_found()
    {
        $connectorAsset = new ConnectorAsset(
            AssetCode::fromString('kartell'),
            []
        );
        $assetIdentifier = AssetIdentifier::fromString('brand_kartell_fingerprint');
        $this->findConnectorAssetsByIdentifiers->save($assetIdentifier, $connectorAsset);

        $assetsFound = $this->findConnectorAssetsByIdentifiers->find([
            'brand_lexon_fingerprint',
            'brand_muuto_fingerprint',
        ], AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            null,
            []
        ));

        $this->assertEquals([], $assetsFound);
    }

    /**
     * @test
     */
    public function it_finds_connector_assets_and_filters_the_values_by_channel()
    {
        $connectorAsset = new ConnectorAsset(
            AssetCode::fromString('kartell'),
            [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'value'   => 'Kartell english label'
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => null,
                        'value'   => 'Kartell french label'
                    ]
                ],
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
        $assetIdentifier = AssetIdentifier::fromString('brand_kartell_fingerprint');

        $this->findConnectorAssetsByIdentifiers->save($assetIdentifier, $connectorAsset);

        $expectedConnectorAsset = new ConnectorAsset(
            AssetCode::fromString('kartell'),
            [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'value'   => 'Kartell english label'
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => null,
                        'value'   => 'Kartell french label'
                    ]
                ],
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

        $assetsFound = $this->findConnectorAssetsByIdentifiers->find([
            $assetIdentifier->normalize(),
        ], AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('brand'),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleIdentifierCollection::empty(),
            10,
            null,
            []
        ));

        $this->assertEquals([$expectedConnectorAsset], $assetsFound);
    }

    /**
     * @test
     */
    public function it_finds_connector_assets_and_filters_the_values_by_locale()
    {
        $connectorAsset = new ConnectorAsset(
            AssetCode::fromString('kartell'),
            [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'value'   => 'Kartell english label'
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => null,
                        'value'   => 'Kartell french label'
                    ]
                ],
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
        $assetIdentifier = AssetIdentifier::fromString('brand_kartell_fingerprint');

        $this->findConnectorAssetsByIdentifiers->save($assetIdentifier, $connectorAsset);

        $expectedConnectorAsset = new ConnectorAsset(
            AssetCode::fromString('kartell'),
            [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'value'   => 'Kartell english label'
                    ],
                ],
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

        $assetsFound = $this->findConnectorAssetsByIdentifiers->find([
            $assetIdentifier->normalize(),
        ], AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('brand'),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleIdentifierCollection::fromNormalized([
                'en_US',
                'de_DE',
            ]),
            10,
            null,
            []
        ));

        $this->assertEquals([$expectedConnectorAsset], $assetsFound);
    }
}
