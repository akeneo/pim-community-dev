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
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlAttributeExistsTest extends SqlIntegrationTestCase
{
    /** @var AttributeExistsInterface */
    private $attributeExists;

    public function setUp()
    {
        parent::setUp();

        $this->attributeExists = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.attribute_exists');
        $this->resetDB();
        $this->loadEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_attribute_exists_for_the_given_identifier()
    {
        $this->loadAttribute('designer', 'name');
        $isExisting = $this->attributeExists->withIdentifier(AttributeIdentifier::create('designer', 'name'));
        Assert::assertTrue($isExisting);
    }

    /**
     * @test
     */
    public function it_returns_false_if_the_attribute_does_not_exist_for_the_given_identifier()
    {
        $isExisting = $this->attributeExists->withIdentifier(AttributeIdentifier::create('designer', 'name'));
        Assert::assertFalse($isExisting);
    }

    /**
     * @test
     */
    public function it_says_if_the_attribute_exists_for_the_given_enriched_entity_identifier_and_order()
    {
        $this->loadAttribute('designer', 'name', 1);

        $isExistingAtOrder1 = $this->attributeExists->withEnrichedEntityIdentifierAndOrder(EnrichedEntityIdentifier::fromString('designer'), AttributeOrder::fromInteger(1));
        $isExistingAtOrder2 = $this->attributeExists->withEnrichedEntityIdentifierAndOrder(EnrichedEntityIdentifier::fromString('designer'), AttributeOrder::fromInteger(2));

        Assert::assertTrue($isExistingAtOrder1);
        Assert::assertFalse($isExistingAtOrder2);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntity(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntity);
    }

    private function loadAttribute(string $enrichedEntityIdentifier, string $identifier, int $order = 0)
    {
        $attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');
        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create($enrichedEntityIdentifier, $identifier),
                EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
                AttributeCode::fromString($identifier),
                LabelCollection::fromArray(['fr_FR' => 'dummy label']),
                AttributeOrder::fromInteger($order),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::none()
            )
        );
    }
}
