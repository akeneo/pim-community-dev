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

namespace Akeneo\AssetManager\Integration\PublicApi\Analytics;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerAssetFamily;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfAttributesPerAssetFamilyTest extends SqlIntegrationTestCase
{
    private SqlAverageMaxNumberOfAttributesPerAssetFamily $averageMaxNumberOfAttributesPerAssetFamily;

    public function setUp(): void
    {
        parent::setUp();

        $this->averageMaxNumberOfAttributesPerAssetFamily = $this->get('akeneo_assetmanager.infrastructure.persistence.query.analytics.average_max_number_of_attributes_per_asset_family');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_number_of_attributes_per_asset_family()
    {
        $this->loadAttributesForAssetFamily(2);
        $this->loadAttributesForAssetFamily(4);
        $this->loadAttributesForAssetFamily(0);

        $volume = $this->averageMaxNumberOfAttributesPerAssetFamily->fetch();

        $this->assertEquals('4', $volume->getMaxVolume());
        $this->assertEquals('3', $volume->getAverageVolume());
    }

    private function loadAttributesForAssetFamily(int $numberOfAttributesPerAssetFamiliestoLoad): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($this->getRandomIdentifier());
        $assetFamilyRepository->create(AssetFamily::create(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        ));

        // By default, there are already 2 attributes created for each asset family
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        for ($i = 0; $i < $numberOfAttributesPerAssetFamiliestoLoad - 2; $i++) {
            $attributeRepository->create(
                TextAttribute::createText(
                    AttributeIdentifier::fromString(sprintf('%s_%d', $i, $assetFamilyIdentifier->normalize())),
                    $assetFamilyIdentifier,
                    AttributeCode::fromString(sprintf('%d', $i)),
                    LabelCollection::fromArray(['en_US' => 'Name']),
                    AttributeOrder::fromInteger($i + 2),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsReadOnly::fromBoolean(false),
                    AttributeValuePerChannel::fromBoolean(true),
                    AttributeValuePerLocale::fromBoolean(true),
                    AttributeMaxLength::fromInteger(155),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty()
                )
            );
        }
    }

    private function getRandomIdentifier(): string
    {
        return str_replace('-', '_', Uuid::uuid4()->toString());
    }
}
