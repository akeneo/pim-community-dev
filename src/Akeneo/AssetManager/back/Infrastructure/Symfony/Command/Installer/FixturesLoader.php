<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\ValueHydratorInterface;

class FixturesLoader
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var ValueHydratorInterface */
    private $valueHydrator;

    /** @var string */
    private $loadedAssetFamily;

    /** @var string[] */
    private $loadedAttributes = [];

    /** @var string[] */
    private $customLoadedAttributes = [];

    /** @var string */
    private $loadedAssetFamilyOfAsset;

    /** @var string */
    private $loadedAssetCode;

    /** @var array */
    private $loadedValues;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AssetRepositoryInterface $assetRepository,
        ValueHydratorInterface $valueHydrator
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->assetRepository = $assetRepository;
        $this->valueHydrator = $valueHydrator;
    }

    public function assetFamily(string $identifier): self
    {
        $this->loadedAssetFamily = $identifier;
        $this->loadedAttributes = [];
        $this->customLoadedAttributes = [];

        return $this;
    }

    public function asset(string $assetFamilyIdentifier, string $assetCode): self
    {
        $this->loadedAssetFamilyOfAsset = $assetFamilyIdentifier;
        $this->loadedAssetCode = $assetCode;
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
        if (null === $this->loadedAssetFamily) {
            throw new \LogicException('You need to call "assetFamily()" first before calling "withAttributes()"');
        }

        $this->loadedAttributes = $attributeCodes;

        return $this;
    }

    public function withAttributeOfTypeText(string $assetFamilyIdentifier, string $attributeCode): self
    {
        $this->customLoadedAttributes[] = TextAttribute::createText(
            $this->attributeRepository->nextIdentifier(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($attributeCode)
            ),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            $this->getOrderForAttribute($attributeCode),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        return $this;
    }

    public function withAttributeOfTypeSingleOption(string $assetFamilyIdentifier, string $attributeCode): self
    {
        $this->customLoadedAttributes[] = OptionAttribute::create(
            $this->attributeRepository->nextIdentifier(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($attributeCode)
            ),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            $this->getOrderForAttribute($attributeCode),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        return $this;
    }

    public function withAttributeOfTypeMultipleOption(string $assetFamilyIdentifier, string $attributeCode)
    {
        $this->customLoadedAttributes[] = OptionCollectionAttribute::create(
            $this->attributeRepository->nextIdentifier(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($attributeCode)
            ),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            $this->getOrderForAttribute('materials'),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        return $this;
    }

    public function load(): array
    {
        if (null !== $this->loadedAssetFamily) {
            $assetFamily = $this->loadAssetFamily();
            $attributes = $this->loadAttributes($assetFamily->getIdentifier());
            $customAttributes = $this->loadCustomAttributes();
        }

        if (null !== $this->loadedAssetCode) {
            $this->loadAssetWithValues();
        }

        $this->loadedAssetFamily = null;
        $this->loadedAttributes = [];
        $this->loadedAssetFamilyOfAsset = null;
        $this->loadedAssetCode = null;

        return [
            'asset_family' => $assetFamily ?? null,
            'attributes' => $attributes ?? [],
            'customAttributes' => $customAttributes ?? []
        ];
    }

    private function loadAssetFamily(): AssetFamily
    {
        $designer = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $brand = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'en_US' => 'Brand',
                'fr_FR' => 'Marque',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $country = AssetFamily::create(
            AssetFamilyIdentifier::fromString('country'),
            [
                'en_US' => 'Country',
                'fr_FR' => 'Pays',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        switch ($this->loadedAssetFamily) {
            case 'designer':
                $this->assetFamilyRepository->create($designer);

                return $designer;
            case 'brand':
                $this->assetFamilyRepository->create($brand);

                return $brand;
            case 'country':
                $this->assetFamilyRepository->create($country);

                return $country;
            default:
                $assetFamily = AssetFamily::create(
                    AssetFamilyIdentifier::fromString($this->loadedAssetFamily),
                    [],
                    Image::createEmpty(),
                    RuleTemplateCollection::empty()
                );
                $this->assetFamilyRepository->create($assetFamily);

                return $assetFamily;
        }
    }

    /**
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     *
     * @return AbstractAttribute[]
     */
    private function loadAttributes(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $attributes = [];

        // NAME
        if (in_array('name', $this->loadedAttributes)) {
            $attributes['name'] = TextAttribute::createText(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('name')
                ),
                $assetFamilyIdentifier,
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
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('email')
                ),
                $assetFamilyIdentifier,
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
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('regex')
                ),
                $assetFamilyIdentifier,
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
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('long_description')
                ),
                $assetFamilyIdentifier,
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
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('main_image')
                ),
                $assetFamilyIdentifier,
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
            $attributes['country'] = AssetAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('country')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('country'),
                LabelCollection::fromArray([
                    'fr_FR' => 'Pays',
                ]),
                $this->getOrderForAttribute('country'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AssetFamilyIdentifier::fromString('country')
            );
        }

        // BRAND
        if (in_array('brand', $this->loadedAttributes)) {
            $attributes['brand'] = AssetAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('brand')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('brand'),
                LabelCollection::fromArray([
                    'fr_FR' => 'Marque',
                ]),
                $this->getOrderForAttribute('brand'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AssetFamilyIdentifier::fromString('brand')
            );
        }

        // BRANDS
        if (in_array('brands', $this->loadedAttributes)) {
            $attributes['brands'] = AssetCollectionAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('brands')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('brands'),
                LabelCollection::fromArray([
                    'fr_FR' => 'Marques',
                ]),
                $this->getOrderForAttribute('brands'),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AssetFamilyIdentifier::fromString('brand')
            );
        }

        // DESIGNERS
        if (in_array('designers', $this->loadedAttributes)) {
            $attributes['designers'] = AssetCollectionAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('designers')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('designers'),
                LabelCollection::fromArray([
                    'en_US' => 'Designers',
                    'fr_FR' => 'Concepteurs',
                ]),
                $this->getOrderForAttribute('designers'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(false),
                AssetFamilyIdentifier::fromString('designer')
            );
        }

        // MATERIALS
        if (in_array('materials', $this->loadedAttributes)) {
            $attributes['materials'] = OptionCollectionAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('materials')
                ),
                $assetFamilyIdentifier,
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
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('nickname')
                ),
                $assetFamilyIdentifier,
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

        // YEAR
        if (in_array('year', $this->loadedAttributes)) {
            $attributes['year'] = NumberAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('year')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('year'),
                LabelCollection::fromArray([
                    'en_US' => 'Year',
                ]),
                $this->getOrderForAttribute('year'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::fromString('0'),
                AttributeLimit::limitless()
            );
        }

        // MAIN MATERIAL
        if (in_array('main_material', $this->loadedAttributes)) {
            $attributes['main_material'] = OptionAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('main_material')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('main_material'),
                LabelCollection::fromArray([
                    'en_US' => 'Main material',
                ]),
                $this->getOrderForAttribute('main_material'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false)
            );
        }

        // WEBSITE
        if (in_array('website', $this->loadedAttributes)) {
            $attributes['website'] = MediaLinkAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('website')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('website'),
                LabelCollection::fromArray([
                    'en_US' => 'Nickname',
                ]),
                $this->getOrderForAttribute('website'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::fromString('https://www.akeneo.com/wp-content/uploads/'),
                Suffix::empty(),
                MediaType::fromString(MediaType::IMAGE)
            );
        }

        // VIDEO
        if (in_array('video', $this->loadedAttributes)) {
            $attributes['video'] = MediaLinkAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('video')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('video'),
                LabelCollection::fromArray([
                   'en_US' => 'Video',
                ]),
                $this->getOrderForAttribute('video'),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::fromString('https://my-stream.com'),
                Suffix::empty(),
                MediaType::fromString(MediaType::OTHER)
            );
        }

        foreach ($attributes as $attribute) {
            $this->attributeRepository->create($attribute);
        }

        return $attributes;
    }

    private function loadAssetWithValues(): void
    {
        $assetIdentifier = $this->assetRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($this->loadedAssetFamilyOfAsset),
            AssetCode::fromString($this->loadedAssetCode)
        );

        $asset = Asset::create(
            $assetIdentifier,
            AssetFamilyIdentifier::fromString($this->loadedAssetFamilyOfAsset),
            AssetCode::fromString($this->loadedAssetCode),
            ValueCollection::fromValues([])
        );

        $attributes = $this->attributeRepository->findByAssetFamily(
            AssetFamilyIdentifier::fromString($this->loadedAssetFamilyOfAsset)
        );
        foreach ($this->loadedValues as $attributeCode => $values) {
            $attribute = current(array_filter($attributes, function (AbstractAttribute $attribute) use ($attributeCode) {
                return (string) $attribute->getCode() === $attributeCode;
            }));

            foreach ($values as $value) {
                $asset->setValue($this->valueHydrator->hydrate($value, $attribute));
            }
        }

        $this->assetRepository->create($asset);
    }

    private function getOrderForAttribute(string $code): AttributeOrder
    {
        $index = array_search($code, $this->loadedAttributes);
        $realIndex = $index + 2; // Because there are always label+image attributes first.

        return AttributeOrder::fromInteger($realIndex);
    }

    private function loadCustomAttributes(): array
    {
        foreach ($this->customLoadedAttributes as $attribute) {
            $this->attributeRepository->create($attribute);
        }

        return $this->customLoadedAttributes;
    }
}
