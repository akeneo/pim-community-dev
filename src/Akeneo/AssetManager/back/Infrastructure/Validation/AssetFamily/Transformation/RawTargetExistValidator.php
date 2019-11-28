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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

class RawTargetExistValidator extends ConstraintValidator
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($rawTarget, Constraint $constraint)
    {
        if (!$constraint instanceof RawTargetExist) {
            throw new UnexpectedTypeException($constraint, RawTargetExist::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($rawTarget, new Assert\Type('array'));
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
            return;
        }

        $attributes = $this->attributeRepository->findByAssetFamily($constraint->getAssetFamilyIdentifier());
        $foundAttribute = null;
        foreach ($attributes as $attribute) {
            if ($attribute->getCode()->__toString() === $rawTarget['attribute']) {
                $foundAttribute = $attribute;

                break;
            }
        }

        if ($foundAttribute instanceof AbstractAttribute) {
            $this->validateAttribute($rawTarget, $foundAttribute);
            return;
        }

        $this->context->buildViolation(
            RawTargetExist::ATTRIBUTE_NOT_FOUND_ERROR,
            ['%attribute_code%' => $rawTarget['attribute']]
        )->atPath('target')->addViolation();
    }

    private function validateAttribute(array $target, AbstractAttribute $attribute): void
    {
        try {
            Target::create(
                $attribute,
                ChannelReference::createfromNormalized($target['channel']),
                LocaleReference::createFromNormalized($target['locale'])
            );
        } catch (\Exception $e) {
            $this->context->buildViolation($e->getMessage())->atPath('target')->addViolation();
        }
    }
}
