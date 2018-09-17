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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindValueKeyCollection extends SqlIntegrationTestCase
{
    /** @var FindValueKeyCollectionInterface */
    private $findValueKeyCollection;

    private $order = 0;

    public function setUp()
    {
        parent::setUp();

        $this->findValueKeyCollection = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_value_key_collection');
        $this->resetDB();
        $this->loadEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_returns_all_attributes()
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetCategoryChannelAndLocale();

        $designer = EnrichedEntityIdentifier::fromString('designer');
        $image = $this->loadAttribute('designer', 'image', false, false);
        $name = $this->loadAttribute('designer', 'name', false, true);
        $age = $this->loadAttribute('designer', 'age', true, false);
        $weight = $this->loadAttribute('designer', 'weigth', true, true);
        $actualValueKeyCollection = ($this->findValueKeyCollection)($designer);

        $this->assertInstanceOf(ValueKeyCollection::class, $actualValueKeyCollection);
        $normalizedActualValueKeyCollection = $actualValueKeyCollection->normalize();
        $this->assertSame(count($normalizedActualValueKeyCollection), 16);
        $this->assertContains(sprintf('%s', $image->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_en_US', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_de_DE', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_fr_FR', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce', $age->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_mobile', $age->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_print', $age->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_en_US', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_mobile_en_US', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_print_en_US', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_de_DE', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_mobile_de_DE', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_print_de_DE', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_fr_FR', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_mobile_fr_FR', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_print_fr_FR', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntity(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            null
        );
        $enrichedEntityRepository->create($enrichedEntity);
    }

    private function loadAttribute(string $enrichedEntityIdentifier, string $attributeCode, bool $hasValuePerChannel, bool $hasValuePerLocale): AbstractAttribute
    {
        $attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $identifier = $attributeRepository->nextIdentifier(
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = TextAttribute::createText(
            $identifier,
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
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
