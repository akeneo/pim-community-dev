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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindImageAttributeCodesTest extends SqlIntegrationTestCase
{
    /** @var FindImageAttributeCodesInterface */
    private $findImageAttributeCodes;

    /** @var AttributeRepositoryInterface */
    private $attributesRepository;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->findImageAttributeCodes = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_image_attribute_codes');
        $this->attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');

        $this->resetDB();
        $this->loadReferenceEntity();
    }

    /**
     * @test
     */
    public function it_returns_the_codes_of_the_image_attributes_for_a_given_reference_entity()
    {
        $this->loadAttributesWithImageType();

        $imageAttributeCodes = ($this->findImageAttributeCodes)(ReferenceEntityIdentifier::fromString('designer'));
        $expectedCodes = [
            AttributeCode::fromString('image'),
            AttributeCode::fromString('main_image'),
            AttributeCode::fromString('second_image')
        ];

        $this->assertEquals($expectedCodes, $imageAttributeCodes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_image_attribute()
    {
        $this->loadAttributesWithoutImageType();

        $imageAttributeCodes = ($this->findImageAttributeCodes)(ReferenceEntityIdentifier::fromString('designer'));

        $this->assertSame([], $imageAttributeCodes);
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function loadAttributesWithImageType(): void
    {
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf'])
        );

        $secondImageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'second_image', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('second_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf'])
        );

        $referenceEntity = $this->referenceEntityRepository
            ->getByIdentifier(ReferenceEntityIdentifier::fromString('designer'));

        $this->attributesRepository->create($imageAttribute);
        $this->attributesRepository->create($secondImageAttribute);
        $this->attributesRepository->deleteByIdentifier($referenceEntity->getAttributeAsLabelReference()->getIdentifier());
    }

    private function loadAttributesWithoutImageType()
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $email = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'email', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('email'),
            LabelCollection::fromArray(['en_US' => 'Email']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );

        $referenceEntity = $this->referenceEntityRepository
            ->getByIdentifier(ReferenceEntityIdentifier::fromString('designer'));

        $this->attributesRepository->create($name);
        $this->attributesRepository->create($email);
        $this->attributesRepository->deleteByIdentifier($referenceEntity->getAttributeAsImageReference()->getIdentifier());
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}
