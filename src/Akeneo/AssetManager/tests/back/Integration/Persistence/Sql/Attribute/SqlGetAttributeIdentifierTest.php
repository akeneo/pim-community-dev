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
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlGetAttributeIdentifierTest extends SqlIntegrationTestCase
{
    private GetAttributeIdentifierInterface $getAttributeIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->getAttributeIdentifier = $this->get('akeneoassetmanager.infrastructure.persistence.query.get_attribute_identifier');
        $this->resetDB();
        $this->loadAssetFamily();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_attribute_exists_for_the_given_identifier()
    {
        $identifier = $this->loadAttribute('designer', 'name');
        $attributeIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('designer'), AttributeCode::fromString('name')
        );
        Assert::assertEquals($identifier, $attributeIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_attribute_identifier_does_not_exist_for_the_given_asset_family_identifier_and_attribute_code()
    {
        $this->expectException(\LogicException::class);
        $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('designer'), AttributeCode::fromString('name')
        );
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

    private function loadAttribute(string $assetFamilyIdentifier, string $attributeCode, int $order = 2): AttributeIdentifier
    {
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $identifier = $attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray(['fr_FR' => 'dummy label']),
                AttributeOrder::fromInteger($order),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        return $identifier;
    }
}
