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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

class RawSourceExistValidator extends ConstraintValidator
{
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

        $validator = Validation::createValidator();
        $violations = $validator->validate($rawSource, new Assert\Type('array'));
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
            return;
        }

        try {
            $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
                AttributeCode::fromString($rawSource['attribute']),
                $constraint->getAssetFamilyIdentifier()
            );
        } catch (AttributeNotFoundException $e) {
            $this->context->buildViolation(
                RawSourceExist::ATTRIBUTE_NOT_FOUND_ERROR,
                ['%attribute_code%' => $rawSource['attribute']]
            )->addViolation();

            return;
        }

        $this->validateAttribute($rawSource, $attribute);
    }

    private function validateAttribute(array $source, AbstractAttribute $attribute): void
    {
        if (!$attribute instanceof MediaFileAttribute) {
            $this->context->buildViolation(RawSourceExist::NOT_MEDIA_FILE_ATTRIBUTE_ERROR)->addViolation();

            return;
        }

        try {
            Source::create(
                $attribute,
                ChannelReference::createFromNormalized($source['channel']),
                LocaleReference::createFromNormalized($source['locale'])
            );
        } catch (\Exception $e) {
            $this->context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
