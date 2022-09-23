<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\AssetCollection;

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\AttributeAsMainMedia;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaFileAsMainMedia;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\MediaLinkAsMainMedia;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionMediaFileSelection;
use Akeneo\Platform\Syndication\Infrastructure\Validation\IsValidAssetAttribute;
use Akeneo\Platform\Syndication\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssetCollectionSelectionValidator extends ConstraintValidator
{
    private GetAttributes $getAttributes;
    private GetAttributeAsMainMediaInterface $getAttributeAsMainMedia;
    /** @var string[] */
    private array $availableCollectionSeparator;

    public function __construct(
        GetAttributes $getAttributes,
        GetAttributeAsMainMediaInterface $getAttributeAsMainMedia,
        array $availableCollectionSeparator
    ) {
        $this->getAttributes = $getAttributes;
        $this->getAttributeAsMainMedia = $getAttributeAsMainMedia;
        $this->availableCollectionSeparator = $availableCollectionSeparator;
    }

    public function validate($selection, Constraint $constraint): void
    {
        if (!$constraint instanceof AssetCollectionSelectionConstraint) {
            throw new \InvalidArgumentException('Invalid constraint');
        }

        $validator = $this->context->getValidator();
        $violations = $validator->validate($selection, [
            new Collection(
                [
                    'fields' => [
                        'type' => new Choice(
                            [
                                'choices' => [
                                    'code',
                                    'label',
                                    'media_file',
                                    'media_link',
                                    'media_file_url',
                                ],
                            ]
                        ),
                        'separator' => new Choice(
                            [
                                'choices' => $this->availableCollectionSeparator,
                            ]
                        ),
                        'locale' => new Optional([new Type(['type' => 'string'])]),
                        'channel' => new Optional([new Type(['type' => 'string'])]),
                        'property' => new Optional([new Type(['type' => 'string'])]),
                        'position' => new Optional([new Type(['type' => 'int'])]),
                        'with_prefix_and_suffix' => new Optional([new Type(['type' => 'bool'])])
                    ],
                ]
            ),
        ]);

        if (0 < $violations->count()) {
            $this->buildViolations($violations);

            return;
        }

        if ('label' === $selection['type']) {
            $violations = $validator->validate($selection['locale'] ?? null, [
                new NotBlank(),
                new LocaleShouldBeActive()
            ]);
            $this->buildViolations($violations, '[locale]');
        } elseif ('media_file' === $selection['type'] || 'media_link' === $selection['type']) {
            $attribute = $this->getAttributes->forCode($constraint->attributeCode);
            if (!$attribute instanceof Attribute) {
                return;
            }

            $assetFamilyCode = $attribute->properties()['reference_data_name'];
            $validator->inContext($this->context)->validate(
                $selection,
                [
                    new Collection(['fields' => ['locale' => new Required(), 'channel' => new Required()], 'allowExtraFields' => true]),
                    new IsValidAssetAttribute(['assetFamilyCode' => $assetFamilyCode]),
                ]
            );

            $attributeAsMainMedia = $this->getAttributeAsMainMedia->forAssetFamilyCode($assetFamilyCode);
            if ('media_file' === $selection['type']) {
                $this->validateMediaFileSelection($selection, $assetFamilyCode, $attributeAsMainMedia, $validator);
            } elseif ('media_link' === $selection['type']) {
                $this->validateMediaLinkSelection($selection, $assetFamilyCode, $attributeAsMainMedia, $validator);
            }
        }
    }

    private function buildViolations(ConstraintViolationListInterface $violations, ?string $path = null): void
    {
        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath($path ?? $violation->getPropertyPath())
                ->addViolation();
        }
    }

    private function validateMediaFileSelection(
        array $selection,
        string $assetFamilyCode,
        AttributeAsMainMedia $attributeAsMainMedia,
        ValidatorInterface $validator
    ): void {
        if (!$attributeAsMainMedia instanceof MediaFileAsMainMedia) {
            $this->context
                ->buildViolation('akeneo.syndication.validation.asset_collection.invalid_type', [
                    'asset_family_code' => $assetFamilyCode
                ])
                ->atPath('[type]')
                ->addViolation();

            return;
        }

        $propertyViolations = $validator->validate($selection, new Collection([
            'fields' => [
                'property' => new Required([
                    new Choice(
                        [
                            'choices' => [
                                AssetCollectionMediaFileSelection::FILE_KEY_PROPERTY,
                                AssetCollectionMediaFileSelection::FILE_PATH_PROPERTY,
                                AssetCollectionMediaFileSelection::ORIGINAL_FILENAME_PROPERTY,
                            ],
                        ]
                    )
                ])
            ],
            'allowExtraFields' => true
        ]));

        $this->buildViolations($propertyViolations);
    }

    private function validateMediaLinkSelection(
        array $selection,
        string $assetFamilyCode,
        AttributeAsMainMedia $attributeAsMainMedia,
        ValidatorInterface $validator
    ): void {
        if (!$attributeAsMainMedia instanceof MediaLinkAsMainMedia) {
            $this->context
                ->buildViolation('akeneo.syndication.validation.asset_collection.invalid_type', [
                    'asset_family_code' => $assetFamilyCode
                ])
                ->atPath('[type]')
                ->addViolation();

            return;
        }

        $withSuffixAndPrefixViolations = $validator->validate($selection, new Collection([
            'fields' => [
                'with_prefix_and_suffix' => new Required()
            ],
            'allowExtraFields' => true
        ]));

        $this->buildViolations($withSuffixAndPrefixViolations);
    }
}
