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
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

class RawSourceExistValidator extends ConstraintValidator
{
    private const ASSET_CODE_PROPERTY = 'code';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($rawSource, Constraint $constraint)
    {
        if (!$constraint instanceof RawSourceExist) {
            throw new UnexpectedTypeException($constraint, RawSourceExist::class);
        }

        if (self::ASSET_CODE_PROPERTY === $rawSource['property']) {
            return;
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($rawSource, new Assert\Type('array'));
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
            return;
        }

        try {
            $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
                AttributeCode::fromString($rawSource['property']),
                $constraint->getAssetFamilyIdentifier()
            );
        } catch (AttributeNotFoundException $e) {
            $this->context->buildViolation(
                RawSourceExist::ATTRIBUTE_NOT_FOUND_ERROR,
                ['%property%' => $rawSource['property']]
            )->addViolation();

            return;
        }

        $this->validateAttribute($rawSource, $attribute);
    }

    private function validateAttribute(array $source, AbstractAttribute $attribute): void
    {
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
