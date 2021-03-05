<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
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
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType as MediaFileMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkMediaType;
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
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

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

    private $namingConvention = null;

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
        $this->namingConvention = null;

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

    public function withAttributeOfTypeText(
        string $assetFamilyIdentifier,
        string $attributeCode,
        bool $valuePerChannel = false,
        bool $valuePerLocale = false
    ): self {
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
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        return $this;
    }

    public function withAttributeOfTypeNumber(
        string $assetFamilyIdentifier,
        string $attributeCode,
        bool $valuePerChannel = false,
        bool $valuePerLocale = false
    ): self {
        $this->customLoadedAttributes[] = NumberAttribute::create(
            $this->attributeRepository->nextIdentifier(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($attributeCode)
            ),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            $this->getOrderForAttribute($attributeCode),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            AttributeDecimalsAllowed::fromBoolean(true),
            AttributeLimit::limitless(),
            AttributeLimit::fromString('100')
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
            AttributeIsReadOnly::fromBoolean(false),
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
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        return $this;
    }

    public function withAttributeOfTypeMediaFile(
        string $assetFamilyIdentifier,
        string $attributeCode,
        bool $scopable = true
    ): self {
        $this->customLoadedAttributes[] = MediaFileAttribute::create(
            $this->attributeRepository->nextIdentifier(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($attributeCode)
            ),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            $this->getOrderForAttribute($attributeCode),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($scopable),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
        );

        return $this;
    }

    public function withNamingConvention(NamingConventionInterface $namingConvention): self
    {
        $this->namingConvention = $namingConvention;

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
            RuleTemplateCollection::createFromProductLinkRules(
                [
                    [
                        'product_selections' => [
                            [
                                'field'    => '{{category_field}}',
                                'operator' => Operators::EQUALS,
                                'value'    => '{{category}}',
                            ],
                        ],
                        'assign_assets_to'   => [
                            [
                                'mode'      => 'add',
                                'attribute' => '{{product_multiple_link}}',
                            ],
                        ],
                    ],
                ]
            )
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
        if (null !== $this->namingConvention) {
            $designer->updateNamingConvention($this->namingConvention);
            $brand->updateNamingConvention($this->namingConvention);
            $country->updateNamingConvention($this->namingConvention);
        }

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
                if (null !== $this->namingConvention) {
                    $assetFamily->updateNamingConvention($this->namingConvention);
                }
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
                AttributeIsReadOnly::fromBoolean(false),
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
                AttributeIsReadOnly::fromBoolean(false),
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
                AttributeIsReadOnly::fromBoolean(false),
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
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(155),
                AttributeIsRichTextEditor::fromBoolean(true)
            );
        }

        // MAIN IMAGE
        if (in_array('main_image', $this->loadedAttributes)) {
            $attributes['main_image'] = MediaFileAttribute::create(
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
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('1000'),
                AttributeAllowedExtensions::fromList(['png']),
                MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
            );
        }

        // MAIN document
        if (in_array('main_document', $this->loadedAttributes)) {
            $attributes['main_document'] = MediaFileAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('main_document')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('main_document'),
                LabelCollection::fromArray([
                    'en_US' => 'Main pdf',
                ]),
                $this->getOrderForAttribute('main_document'),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('1000'),
                AttributeAllowedExtensions::fromList(['pdf']),
                MediaFileMediaType::fromString(MediaFileMediaType::PDF)
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
                AttributeIsReadOnly::fromBoolean(false),
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
                AttributeIsReadOnly::fromBoolean(false),
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
                AttributeIsReadOnly::fromBoolean(false),
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
                AttributeIsReadOnly::fromBoolean(false),
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
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::fromString('https://www.akeneo.com/wp-content/uploads/'),
                Suffix::empty(),
                MediaLinkMediaType::fromString(MediaLinkMediaType::IMAGE)
            );
        }

        // NOTICE
        if (in_array('notice', $this->loadedAttributes)) {
            $attributes['notice'] = MediaLinkAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('notice')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('notice'),
                LabelCollection::fromArray([
                    'en_US' => 'Nickname',
                ]),
                $this->getOrderForAttribute('notice'),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::fromString('https://www.akeneo.com/wp-content/uploads/'),
                Suffix::empty(),
                MediaLinkMediaType::fromString(MediaLinkMediaType::PDF)
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
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::fromString('https://my-stream.com'),
                Suffix::empty(),
                MediaLinkMediaType::fromString(MediaLinkMediaType::OTHER)
            );
        }

        // Youtube
        if (in_array('youtube', $this->loadedAttributes)) {
            $attributes['youtube'] = MediaLinkAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('youtube')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('youtube'),
                LabelCollection::fromArray([
                    'en_US' => 'Youtube link',
                ]),
                $this->getOrderForAttribute('youtube'),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::empty(),
                Suffix::empty(),
                MediaLinkMediaType::fromString(MediaLinkMediaType::YOUTUBE)
            );
        }

        // Vimeo
        if (in_array('vimeo', $this->loadedAttributes)) {
            $attributes['vimeo'] = MediaLinkAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('vimeo')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('vimeo'),
                LabelCollection::fromArray([
                    'en_US' => 'Vimeo link',
                ]),
                $this->getOrderForAttribute('vimeo'),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::empty(),
                Suffix::empty(),
                MediaLinkMediaType::fromString(MediaLinkMediaType::VIMEO)
            );
        }

        // TARGET IMAGE
        if (in_array('target_image', $this->loadedAttributes)) {
            $attributes['target_image'] = MediaFileAttribute::create(
                $this->attributeRepository->nextIdentifier(
                    $assetFamilyIdentifier,
                    AttributeCode::fromString('target_image')
                ),
                $assetFamilyIdentifier,
                AttributeCode::fromString('target_image'),
                LabelCollection::fromArray([]),
                $this->getOrderForAttribute('target_image'),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::noLimit(),
                AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED),
                MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
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
            if (!$attribute) {
                throw new \RuntimeException(sprintf('Impossible to load value for attribute "%s", attribute not found.', $attributeCode));
            }

            foreach ($values as $value) {
                $asset->setValue($this->valueHydrator->hydrate($value, $attribute));
            }
        }

        $this->assetRepository->create($asset);
    }

    private function getOrderForAttribute(string $code): AttributeOrder
    {
        $index = array_search($code, $this->loadedAttributes);
        $realIndex = $index + 2; // Because there are always label+media file attributes first.

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
