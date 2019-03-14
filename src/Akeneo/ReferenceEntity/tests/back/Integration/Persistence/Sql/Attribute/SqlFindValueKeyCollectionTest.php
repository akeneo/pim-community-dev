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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindValueKeyCollectionTest extends SqlIntegrationTestCase
{
    /** @var FindValueKeyCollectionInterface */
    private $findValueKeyCollection;

    private $order = 2;

    public function setUp(): void
    {
        parent::setUp();

        $this->findValueKeyCollection = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_value_key_collection');
        $this->resetDB();
        $this->loadReferenceEntity();
    }

    /**
     * @test
     */
    public function it_returns_all_attributes()
    {
        $designer = ReferenceEntityIdentifier::fromString('designer');
        $image = $this->loadAttribute('designer', 'main_image', false, false);
        $name = $this->loadAttribute('designer', 'name', false, true);
        $age = $this->loadAttribute('designer', 'age', true, false);
        $weight = $this->loadAttribute('designer', 'weigth', true, true);

        $actualValueKeyCollection = ($this->findValueKeyCollection)($designer);

        /** @var ReferenceEntity $referenceEntity */
        $referenceEntity = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')
            ->getByIdentifier($designer);
        $attributeAsLabelIdentifier = $referenceEntity->getAttributeAsLabelReference()->getIdentifier();
        $attributeAsImageIdentifier = $referenceEntity->getAttributeAsImageReference()->getIdentifier();

        $this->assertInstanceOf(ValueKeyCollection::class, $actualValueKeyCollection);
        $normalizedActualValueKeyCollection = $actualValueKeyCollection->normalize();
        $this->assertSame(count($normalizedActualValueKeyCollection), 15);
        $this->assertContains(sprintf('%s', $image->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_en_US', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_de_DE', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_fr_FR', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce', $age->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_mobile', $age->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_print', $age->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_print_en_US', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_mobile_de_DE', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_fr_FR', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_en_US', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_en_US', $attributeAsLabelIdentifier), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_fr_FR', $attributeAsLabelIdentifier), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_de_DE', $attributeAsLabelIdentifier), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s', $attributeAsImageIdentifier), $normalizedActualValueKeyCollection);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function loadAttribute(string $referenceEntityIdentifier, string $attributeCode, bool $hasValuePerChannel, bool $hasValuePerLocale): AbstractAttribute
    {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $identifier = $attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = TextAttribute::createText(
            $identifier,
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
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
