<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RawSourceValidator extends ConstraintValidator
{
    private const ASSET_CODE_PROPERTY = 'code';
    private const ATTRIBUTE_AS_MAIN_MEDIA_PROPERTY = 'attribute_as_main_media';

    /** @var FindAssetFamilyAttributeAsMainMediaInterface */
    private $findAttributeAsMainMedia;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        FindAssetFamilyAttributeAsMainMediaInterface $findAttributeAsMainMedia,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->findAttributeAsMainMedia = $findAttributeAsMainMedia;
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($rawSource, Constraint $constraint)
    {
        if (!$constraint instanceof RawSource) {
            throw new UnexpectedTypeException($constraint, RawSource::class);
        }

        if (self::ASSET_CODE_PROPERTY === $rawSource['property']) {
            return;
        } elseif (self::ATTRIBUTE_AS_MAIN_MEDIA_PROPERTY !== $rawSource['property']) {
            $this->context->buildViolation(
                RawSource::INVALID_PROPERTY_ERROR,
                [
                    '%property%' => $rawSource['property'],
                    '%expected_properties%' => implode('", "', [self::ASSET_CODE_PROPERTY, self::ATTRIBUTE_AS_MAIN_MEDIA_PROPERTY])
                ]
            )->addViolation();

            return;
        }

        $this->validateAttributeAsMainMedia($rawSource, $constraint->getAssetFamilyIdentifier());
    }

    private function validateAttributeAsMainMedia(array $source, AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $attributeAsMainMedia = $this->findAttributeAsMainMedia->find($assetFamilyIdentifier);
        if ($attributeAsMainMedia->isEmpty()) {
            $this->context->buildViolation(
                RawSource::NO_ATTRIBUTE_AS_MAIN_MEDIA,
                [
                    '%asset_family%' => $assetFamilyIdentifier->__toString(),
                ]
            )->addViolation();

            return;
        }
        $attribute = $this->attributeRepository->getByIdentifier($attributeAsMainMedia->getIdentifier());

        $channelReference = ChannelReference::createfromNormalized($source['channel']);
        $localeReference = LocaleReference::createFromNormalized($source['locale']);

        $attribute->hasValuePerChannel() ?
            (
                $channelReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is scopable, you must define a channel', (string) $attribute->getCode())
                )->addViolation()
            ) : (
                !$channelReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is not scopable, you cannot define a channel', (string) $attribute->getCode())
                )->addViolation()
            );

        $attribute->hasValuePerLocale() ?
            (
                $localeReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is localizable, you must define a locale', (string) $attribute->getCode())
                )->addViolation()
            ) : (
                !$localeReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is not localizable, you cannot define a locale', (string) $attribute->getCode())
                )->addViolation()
            );
    }
}
