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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Suffix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ValueHydratorInterface;

class FixturesLoader
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var ValueHydratorInterface */
    private $valueHydrator;

    /** @var string */
    private $loadedRefEntity;

    /** @var string[] */
    private $loadedAttributes = [];

    /** @var string */
    private $loadedRecordRefEntity;

    /** @var string */
    private $loadedRecordCode;

    /** @var array */
    private $loadedValues;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        RecordRepositoryInterface $recordRepository,
        ValueHydratorInterface $valueHydrator
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordRepository = $recordRepository;
        $this->valueHydrator = $valueHydrator;
    }

    public function referenceEntity(string $identifier): self
    {
        $this->loadedRefEntity = $identifier;
        $this->loadedAttributes = [];

        return $this;
    }

    public function record(string $referenceEntityIdentifier, string $recordCode): self
    {
        $this->loadedRecordRefEntity = $referenceEntityIdentifier;
        $this->loadedRecordCode = $recordCode;
        $this->loadedValues = [];

        return $this;
    }

    public function withValues(array $values): self
    {
        $this->loadedValues = $values;

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
        if (null !== $this->loadedRefEntity) {
            $referenceEntity = $this->loadReferenceEntity();
            $attributes = $this->loadAttributes($referenceEntity->getIdentifier());
        }

        if (null !== $this->loadedRecordCode) {
            $record = $this->loadRecord();
            $this->loadValues($record->getIdentifier());
        }

        $this->loadedRefEntity = null;
        $this->loadedAttributes = [];
        $this->loadedRecordRefEntity = null;
        $this->loadedRecordCode = null;

        return [
            'reference_entity' => $referenceEntity ?? null,
            'attributes' => $attributes ?? []
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

        // BRAND
        if (in_array('brand', $this->loadedAttributes)) {
            $attributes['brand'] = RecordAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('brand')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('brand'),
                LabelCollection::fromArray([
                    'fr_FR' => 'Marque',
                ]),
                $this->getOrderForAttribute('brand'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                ReferenceEntityIdentifier::fromString('brand')
            );
        }

        // BRANDS
        if (in_array('brands', $this->loadedAttributes)) {
            $attributes['brands'] = RecordCollectionAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('brands')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('brands'),
                LabelCollection::fromArray([
                    'fr_FR' => 'Marques',
                ]),
                $this->getOrderForAttribute('brands'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                ReferenceEntityIdentifier::fromString('brand')
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

        // WEBSITE
        if (in_array('website', $this->loadedAttributes)) {
            $attributes['website'] = UrlAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $referenceEntityIdentifier,
                    AttributeCode::fromString('website')
                ),
                $referenceEntityIdentifier,
                AttributeCode::fromString('website'),
                LabelCollection::fromArray([
                    'en_US' => 'Nickname',
                ]),
                $this->getOrderForAttribute('website'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::fromString('https://my-dam.com/'),
                Suffix::fromString('/500x500'),
                MediaType::fromString('image')
            );
        }

        foreach ($attributes as $attribute) {
            $this->attributeRepository->create($attribute);
        }

        return $attributes;
    }

    private function loadRecord(): Record
    {
        $recordIdentifier = $this->recordRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($this->loadedRecordRefEntity),
            RecordCode::fromString($this->loadedRecordCode)
        );

        $record = Record::create(
            $recordIdentifier,
            ReferenceEntityIdentifier::fromString($this->loadedRecordRefEntity),
            RecordCode::fromString($this->loadedRecordCode),
            ValueCollection::fromValues([])
        );

        $this->recordRepository->create($record);

        return $record;
    }

    private function loadValues(RecordIdentifier $recordIdentifier): void
    {
        $record = $this->recordRepository->getByIdentifier($recordIdentifier);
        $attributes = $this->attributeRepository->findByReferenceEntity(
            ReferenceEntityIdentifier::fromString($this->loadedRecordRefEntity)
        );

        foreach ($this->loadedValues as $attributeCode => $values) {
            $attribute = current(array_filter($attributes, function (AbstractAttribute $attribute) use ($attributeCode) {
                return (string) $attribute->getCode() === $attributeCode;
            }));

            foreach ($values as $value) {
                $record->setValue($this->valueHydrator->hydrate($value, $attribute));
            }
        }

        $this->recordRepository->update($record);
    }

    private function getOrderForAttribute(string $code): AttributeOrder
    {
        $index = array_search($code, $this->loadedAttributes);
        $realIndex = $index + 2; // Because there are always label+image attributes first.

        return AttributeOrder::fromInteger($realIndex);
    }
}
