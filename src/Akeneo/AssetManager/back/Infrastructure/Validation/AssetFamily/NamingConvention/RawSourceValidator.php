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
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RawSourceValidator extends ConstraintValidator
{
    private const ASSET_CODE_PROPERTY = 'code';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindAssetFamilyAttributeAsMainMediaInterface */
    private $findAttributeAsMainMedia;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FindAssetFamilyAttributeAsMainMediaInterface $findAttributeAsMainMedia
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->findAttributeAsMainMedia = $findAttributeAsMainMedia;
    }

    public function validate($rawSource, Constraint $constraint)
    {
        if (!$constraint instanceof RawSource) {
            throw new UnexpectedTypeException($constraint, RawSource::class);
        }

        if (self::ASSET_CODE_PROPERTY === $rawSource['property']) {
            return;
        }

        try {
            $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
                AttributeCode::fromString($rawSource['property']),
                $constraint->getAssetFamilyIdentifier()
            );
        } catch (AttributeNotFoundException $e) {
            $this->context->buildViolation(
                RawSource::ATTRIBUTE_NOT_FOUND_ERROR,
                ['%property%' => $rawSource['property']]
            )->addViolation();

            return;
        }

        $attributeAsMainMedia = $this->findAttributeAsMainMedia->find($constraint->getAssetFamilyIdentifier());
        if ($attributeAsMainMedia->isEmpty() || !$attributeAsMainMedia->getIdentifier()->equals($attribute->getIdentifier())) {
            $this->context->buildViolation(
                RawSource::ATTRIBUTE_IS_NOT_MAIN_MEDIA,
                ['%property%' => $rawSource['property']]
            )->addViolation();

            return;
        }

        $this->validateAttribute($rawSource, $attribute);
    }

    private function validateAttribute(array $source, AbstractAttribute $attribute): void
    {
        $channelReference = ChannelReference::createFromNormalized($source['channel']);
        $localeReference = LocaleReference::createFromNormalized($source['locale']);

        $attribute->hasValuePerChannel() ?
            ($channelReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is scopable, you must define a channel', (string) $attribute->getCode())
                )->addViolation()) : (!$channelReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is not scopable, you cannot define a channel', (string) $attribute->getCode())
                )->addViolation());

        $attribute->hasValuePerLocale() ?
            ($localeReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is localizable, you must define a locale', (string) $attribute->getCode())
                )->addViolation()) : (!$localeReference->isEmpty() && $this->context->buildViolation(
                    sprintf('Attribute "%s" is not localizable, you cannot define a locale', (string) $attribute->getCode())
                )->addViolation());
    }
}
