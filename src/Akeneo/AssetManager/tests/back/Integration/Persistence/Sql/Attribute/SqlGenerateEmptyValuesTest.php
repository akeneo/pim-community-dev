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
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
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
use Akeneo\AssetManager\Domain\Query\Asset\GenerateEmptyValuesInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlGenerateEmptyValuesTest extends SqlIntegrationTestCase
{
    private GenerateEmptyValuesInterface $generateEmptyValues;

    private int $order = 2;

    public function setUp(): void
    {
        parent::setUp();

        $this->generateEmptyValues = $this->get('akeneo_assetmanager.infrastructure.persistence.query.generate_empty_values');
        $this->resetDB();
        $this->loadAssetFamily();
    }

    /**
     * @test
     */
    public function it_returns_all_empty_values_possible_for_a_given_asset_family()
    {
        $designer = AssetFamilyIdentifier::fromString('designer');
        $image = $this->loadAttribute('designer', 'main_image', false, false);
        $name = $this->loadAttribute('designer', 'name', false, true);
        $age = $this->loadAttribute('designer', 'age', true, false);
        $weight = $this->loadAttribute('designer', 'weigth', true, true);
        $emptyValues = $this->generateEmptyValues->generate($designer);

        /** @var AssetFamily $assetFamily */
        $assetFamily = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')
            ->getByIdentifier($designer);
        $attributeAsLabelIdentifier = $assetFamily->getAttributeAsLabelReference()->getIdentifier();
        $attributeAsMainMediaIdentifier = $assetFamily->getAttributeAsMainMediaReference()->getIdentifier();

        $this->assertCount(15, $emptyValues);
        $this->assertArrayHasKey(sprintf('%s', $image->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_en_US', $name->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_de_DE', $name->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_fr_FR', $name->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_ecommerce', $age->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_mobile', $age->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_print', $age->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_print_en_US', $weight->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_mobile_de_DE', $weight->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_ecommerce_fr_FR', $weight->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_ecommerce_en_US', $weight->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_en_US', $attributeAsLabelIdentifier), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_fr_FR', $attributeAsLabelIdentifier), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_de_DE', $attributeAsLabelIdentifier), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s', $attributeAsMainMediaIdentifier), $emptyValues);

        $this->assertSame([
            'data' => null,
            'locale' => null,
            'channel' => null,
            'attribute' => $image->normalize(),
        ], $emptyValues[sprintf('%s', $image->getIdentifier())]);

        $this->assertSame([
            'data' => null,
            'locale' => 'de_DE',
            'channel' => null,
            'attribute' => $name->normalize(),
        ], $emptyValues[sprintf('%s_de_DE', $name->getIdentifier())]);

        $this->assertSame([
            'data' => null,
            'locale' => null,
            'channel' => 'mobile',
            'attribute' => $age->normalize(),
        ], $emptyValues[sprintf('%s_mobile', $age->getIdentifier())]);

        $this->assertSame([
            'data' => null,
            'locale' => 'fr_FR',
            'channel' => 'ecommerce',
            'attribute' => $weight->normalize(),
        ], $emptyValues[sprintf('%s_ecommerce_fr_FR', $weight->getIdentifier())]);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamily(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    private function loadAttribute(string $assetFamilyIdentifier, string $attributeCode, bool $hasValuePerChannel, bool $hasValuePerLocale): AbstractAttribute
    {
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $identifier = $attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = TextAttribute::createText(
            $identifier,
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($hasValuePerChannel),
            AttributeValuePerLocale::fromBoolean($hasValuePerLocale),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $attributeRepository->create($attribute);

        return $attribute;
    }
}
