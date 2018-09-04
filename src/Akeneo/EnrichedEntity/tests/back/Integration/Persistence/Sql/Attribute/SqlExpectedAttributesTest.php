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

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
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
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ExpectedAttributesInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlExpectedAttributesTest extends SqlIntegrationTestCase
{
    /** @var ExpectedAttributesInterface */
    private $expectedAttributes;

    private $order = 0;

    public function setUp()
    {
        parent::setUp();

        $this->expectedAttributes = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.expected_attributes');
        $this->resetDB();
        $this->loadEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_returns_things()
    {
        $designer = EnrichedEntityIdentifier::fromString('designer');
        $this->loadAttribute('designer', 'image', false, false, false);
        $this->loadAttribute('designer', 'description', false, false, true);
        $this->loadAttribute('designer', 'name', false, true, true);
        $this->loadAttribute('designer', 'height', false, true, false);
        $this->loadAttribute('designer', 'weigth', true, true, true);
        $this->loadAttribute('designer', 'popularity', true, true, false);
        $this->loadAttribute('designer', 'age', true, false, false);
        // $this->loadAttribute('brand', 'name', true, false, true);
        $results = $this->expectedAttributes->withEnrichedEntityIdentifier($designer);
        // Assert::assertTrue($isExisting);
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

    private function loadAttribute(string $enrichedEntityIdentifier, string $attributeCode, bool $hasValuePerChannel, bool $hasValuePerLocale, bool $isRequired): AttributeIdentifier
    {
        $attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $identifier = $attributeRepository->nextIdentifier(
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray(['fr_FR' => 'dummy label']),
                AttributeOrder::fromInteger($this->order++),
                AttributeIsRequired::fromBoolean($isRequired),
                AttributeValuePerChannel::fromBoolean($hasValuePerChannel),
                AttributeValuePerLocale::fromBoolean($hasValuePerLocale),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        return $identifier;
    }
}
