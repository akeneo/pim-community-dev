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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindAttributeNextOrderTest extends SqlIntegrationTestCase
{
    /** @var FindAttributeNextOrderInterface */
    private $findAttributeNextOrder;

    public function setUp()
    {
        parent::setUp();

        $this->findAttributeNextOrder = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_attribute_next_order');
        $this->resetDB();
        $this->loadReferenceEntitiesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_next_order_if_the_reference_entity_already_have_attributes()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $nextOrder = $this->findAttributeNextOrder->withReferenceEntityIdentifier($referenceEntityIdentifier);

        $this->assertEquals(AttributeOrder::fromInteger(1), $nextOrder);
    }

    /**
     * @test
     */
    public function it_returns_zero_if_the_reference_entity_does_not_have_any_attribute_yet()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');

        $nextOrder = $this->findAttributeNextOrder->withReferenceEntityIdentifier($referenceEntityIdentifier);

        $this->assertEquals(AttributeOrder::fromInteger(0), $nextOrder);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntitiesAndAttributes(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $referenceEntityFull = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntityFull);

        $identifier = $attributesRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name')
        );

        $textAttribute = TextAttribute::createText(
            $identifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributesRepository->create($textAttribute);

        $referenceEntityEmpty = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntityEmpty);
    }
}
