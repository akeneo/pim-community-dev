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
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocaleInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindRequiredValueKeyCollectionForChannelAndLocaleTest extends SqlIntegrationTestCase
{
    /** @var FindRequiredValueKeyCollectionForChannelAndLocaleInterface */
    private $findRequiredValueKeyCollection;

    private $order = 0;

    public function setUp()
    {
        parent::setUp();

        $this->findRequiredValueKeyCollection = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_required_value_key_collection_for_channel_and_locale');
        $this->resetDB();
        $this->loadReferenceEntity();
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_collection_of_required_attributes_of_a_reference_entity()
    {
        $designer = ReferenceEntityIdentifier::fromString('designer');
        $mobile = ChannelIdentifier::fromCode('mobile');
        $french = LocaleIdentifier::fromCode('fr_FR');

        $image = $this->loadAttribute('designer', 'image', true, false, false);
        $name = $this->loadAttribute('designer', 'name', true, false, true);
        $age = $this->loadAttribute('designer', 'age', false, false, false);
        $weight = $this->loadAttribute('designer', 'weigth', false, false, false);
        $actualValueKeyCollection = ($this->findRequiredValueKeyCollection)($designer, $mobile, $french);

        $this->assertInstanceOf(ValueKeyCollection::class, $actualValueKeyCollection);
        $normalizedActualValueKeyCollection = $actualValueKeyCollection->normalize();
        $this->assertSame(count($normalizedActualValueKeyCollection), 2);
        $this->assertContains(sprintf('%s', $image->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_fr_FR', $name->getIdentifier()), $normalizedActualValueKeyCollection);

        $this->assertNotContains(sprintf('%s', $age->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertNotContains(sprintf('%s', $weight->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertNotContains(sprintf('%s_de_DE', $name->getIdentifier()), $normalizedActualValueKeyCollection);
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

    private function loadAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        bool $isRequired,
        bool $hasValuePerChannel,
        bool $hasValuePerLocale
    ): AbstractAttribute {
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
            AttributeIsRequired::fromBoolean($isRequired),
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
