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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAttributesByAssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorAttributesByAssetFamilyIdentifierTest extends TestCase
{
    /** @var InMemoryFindConnectorAttributesByAssetFamilyIdentifier */
    private $query;

    public function setUp(): void
    {
        parent::setUp();
        $this->query = new InMemoryFindConnectorAttributesByAssetFamilyIdentifier();
    }

    /**
     * @test
     */
    public function it_returns_null_when_finding_a_non_existent_asset_family()
    {
        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            'text',
            AttributeValuePerLocale::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            []
        );

        $result = $this->query->find(
            AssetFamilyIdentifier::fromString('non_existent_asset_family_identifier'),
            $connectorAttribute
        );

        Assert::assertEmpty($result);
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_when_finding_an_existing_asset_family()
    {
        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            'text',
            AttributeValuePerLocale::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            []
        );

        $this->query->save(
            AssetFamilyIdentifier::fromString('existent_asset_family_identifier'),
            $connectorAttribute
        );

        $results = $this->query->find(
            AssetFamilyIdentifier::fromString('existent_asset_family_identifier'),
            $connectorAttribute
        );

        Assert::assertNotNull($results);
        Assert::assertSame([
            $connectorAttribute
        ], $results);
    }
}
