<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Helper;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;

class FixturesLoader
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var string */
    private $loadedRefEntity;

    /** @var string[] */
    private $loadedAttributes = [];

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function referenceEntity(string $identifier): self
    {
        $this->loadedRefEntity = $identifier;
        $this->loadedAttributes = [];

        return $this;
    }

    public function withAttributes(array $attributeCodes): self
    {
        if (null === $this->loadedRefEntity) {
            throw new \LogicException('You need to call "referenceEntity()" first before calling "withAttributes()"');
        }

        $this->loadedAttributes = $attributeCodes;

        return $this;
    }

    public function load(): array
    {
        $referenceEntity = $this->loadReferenceEntity();
        $attributes = $this->loadAttributes($referenceEntity->getIdentifier());

        return [
            'reference_entity' => $referenceEntity,
            'attributes' => $attributes
        ];
    }

    private function loadReferenceEntity(): ReferenceEntity
    {
        $designer = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
            Image::createEmpty()
        );

        $brand = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'en_US' => 'Brand',
                'fr_FR' => 'Marque',
            ],
            Image::createEmpty()
        );

        $country = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('country'),
            [
                'en_US' => 'Country',
                'fr_FR' => 'Pays',
            ],
            Image::createEmpty()
        );

        switch ($this->loadedRefEntity) {
            case 'designer':
                $this->referenceEntityRepository->create($designer);

                return $designer;
            case 'brand':
                $this->referenceEntityRepository->create($brand);

                return $brand;
            case 'country':
                $this->referenceEntityRepository->create($country);

                return $country;
            default:
                throw new \LogicException(
                    sprintf(
                        'Fixtures Loader has no fixtures for reference entity with identifier %s',
                        $this->loadedRefEntity
                    )
                );
        }
    }

    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     *
     * @return AbstractAttribute[]
     */
    private function loadAttributes(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $attributes = [];

        // NAME
        if (in_array('name', $this->loadedAttributes)) {
            $attributes['name'] = TextAttribute::createText(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('name')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('name'),
                LabelCollection::fromArray([
                    'en_US' => 'Name',
                    'fr_FR' => 'Nom',
                ]),
                $this->getOrderForAttribute('name'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            );
        }

        // EMAIL
        if (in_array('email', $this->loadedAttributes)) {
            $attributes['email'] = TextAttribute::createText(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('email')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('email'),
                LabelCollection::fromArray([
                    'en_US' => 'Email',
                    'fr_FR' => 'Email',
                ]),
                $this->getOrderForAttribute('email'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(155),
                AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
                AttributeRegularExpression::createEmpty()
            );
        }

        // REGEX
        if (in_array('regex', $this->loadedAttributes)) {
            $attributes['regex'] = TextAttribute::createText(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('regex')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('regex'),
                LabelCollection::fromArray([
                    'en_US' => 'Regex',
                ]),
                $this->getOrderForAttribute('regex'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(155),
                AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
                AttributeRegularExpression::fromString('/\w+/')
            );
        }

        // LONG DESCRIPTION
        if (in_array('long_description', $this->loadedAttributes)) {
            $attributes['long_description'] = TextAttribute::createTextarea(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('long_description')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('long_description'),
                LabelCollection::fromArray([
                    'en_US' => 'Long description',
                ]),
                $this->getOrderForAttribute('long_description'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(155),
                AttributeIsRichTextEditor::fromBoolean(true)
            );
        }

        // MAIN IMAGE
        if (in_array('main_image', $this->loadedAttributes)) {
            $attributes['main_image'] = ImageAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('main_image')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('main_image'),
                LabelCollection::fromArray([
                    'en_US' => 'Portrait',
                ]),
                $this->getOrderForAttribute('main_image'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('1000'),
                AttributeAllowedExtensions::fromList(['png'])
            );
        }

        // COUNTRY
        if (in_array('country', $this->loadedAttributes)) {
            $attributes['country'] = RecordAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('country')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('country'),
                LabelCollection::fromArray([
                    'fr_FR' => 'Pays',
                ]),
                $this->getOrderForAttribute('country'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                ReferenceEntityIdentifier::fromString('country')
            );
        }

        // MATERIALS
        if (in_array('materials', $this->loadedAttributes)) {
            $attributes['materials'] = OptionCollectionAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('materials')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('materials'),
                LabelCollection::fromArray([
                    'fr_FR' => 'Materiaux',
                ]),
                $this->getOrderForAttribute('materials'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(true)
            );
        }

        // NICKNAME
        if (in_array('nickname', $this->loadedAttributes)) {
            $attributes['nickname'] = TextAttribute::createText(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('nickname')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('nickname'),
                LabelCollection::fromArray([
                    'en_US' => 'Nickname',
                ]),
                $this->getOrderForAttribute('nickname'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(100),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            );
        }

        foreach ($attributes as $attribute) {
            $this->attributeRepository->create($attribute);
        }

        return $attributes;
    }

    private function getOrderForAttribute(string $code): AttributeOrder
    {
        $index = array_search($code, $this->loadedAttributes);
        $realIndex = $index + 2; // Because there are always label+image attributes first.

        return AttributeOrder::fromInteger($realIndex);
    }
}
