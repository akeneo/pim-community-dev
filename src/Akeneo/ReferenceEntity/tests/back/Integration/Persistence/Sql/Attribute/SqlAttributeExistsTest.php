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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlAttributeExistsTest extends SqlIntegrationTestCase
{
    /** @var AttributeExistsInterface */
    private $attributeExists;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributeExists = $this->get('akeneo_referenceentity.infrastructure.persistence.query.attribute_exists');
        $this->resetDB();
        $this->loadReferenceEntity();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_attribute_exists_for_the_given_identifier()
    {
        $identifier = $this->loadAttribute('designer', 'name');
        $isExisting = $this->attributeExists->withIdentifier($identifier);
        Assert::assertTrue($isExisting);
    }

    /**
     * @test
     */
    public function it_returns_false_if_the_attribute_does_not_exist_for_the_given_identifier()
    {
        $isExisting = $this->attributeExists->withIdentifier(AttributeIdentifier::create('designer', 'name', 'none'));
        Assert::assertFalse($isExisting);
    }

    /**
     * @test
     */
    public function it_says_if_the_attribute_exists_for_the_given_reference_entity_identifier_and_order()
    {
        $this->loadAttribute('designer', 'name', 3);

        $isExistingAtOrder1 = $this->attributeExists->withReferenceEntityIdentifierAndOrder(ReferenceEntityIdentifier::fromString('designer'), AttributeOrder::fromInteger(1));
        $isExistingAtOrder2 = $this->attributeExists->withReferenceEntityIdentifierAndOrder(ReferenceEntityIdentifier::fromString('designer'), AttributeOrder::fromInteger(2));

        Assert::assertTrue($isExistingAtOrder1);
        Assert::assertFalse($isExistingAtOrder2);
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

    private function loadAttribute(string $referenceEntityIdentifier, string $attributeCode, int $order = 3): AttributeIdentifier
    {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $identifier = $attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray(['fr_FR' => 'dummy label']),
                AttributeOrder::fromInteger($order),
                AttributeIsRequired::fromBoolean(false),
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
