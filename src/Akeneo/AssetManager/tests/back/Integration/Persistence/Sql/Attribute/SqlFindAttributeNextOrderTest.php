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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
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
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAttributeNextOrderTest extends SqlIntegrationTestCase
{
    private FindAttributeNextOrderInterface $findAttributeNextOrder;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributeNextOrder = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_attribute_next_order');
        $this->resetDB();
        $this->loadAssetFamiliesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_next_order_if_the_asset_family_already_have_attributes()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $nextOrder = $this->findAttributeNextOrder->withAssetFamilyIdentifier($assetFamilyIdentifier);

        $this->assertEquals(AttributeOrder::fromInteger(3), $nextOrder);
    }

    /**
     * @test
     */
    public function it_returns_zero_if_the_asset_family_does_not_have_any_attribute_yet()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');

        $nextOrder = $this->findAttributeNextOrder->withAssetFamilyIdentifier($assetFamilyIdentifier);

        $this->assertEquals(AttributeOrder::fromInteger(2), $nextOrder);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamiliesAndAttributes(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $attributesRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $assetFamilyFull = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamilyFull);

        $identifier = $attributesRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name')
        );

        $textAttribute = TextAttribute::createText(
            $identifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributesRepository->create($textAttribute);

        $assetFamilyEmpty = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamilyEmpty);
    }
}
