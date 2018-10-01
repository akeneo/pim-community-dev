<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AllowedExtensionsValidator extends ConstraintValidator
{
    public function validate($allowedExtensions, Constraint $constraint)
    {
        if (!$constraint instanceof AllowedExtensions) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        if ($this->isNotArray($allowedExtensions)) {
            return;
        }

        if (AttributeAllowedExtensions::ALL_ALLOWED === $allowedExtensions) {
            return;
        }

        if ($this->isNotArrayOfStrings($allowedExtensions)) {
            return;
        }

        $this->checkExtensionsAreValid($allowedExtensions);
    }

    private function addViolations(ConstraintViolationListInterface $violations): void
    {
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation($violation->getMessage(), $violation->getParameters());
            }
        }
    }

    private function isNotArray($allowedExtensions): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($allowedExtensions, [
            new Assert\Type('array'),
        ]);

        $notValid = $violations->count() > 0;
        if ($notValid) {
            $this->addViolations($violations);
        }

        return $notValid;
    }

    private function isNotArrayOfStrings($allowedExtensions): bool
    {
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();
        foreach ($allowedExtensions as $allowedExtension) {
            $violations = $validator->validate($allowedExtension, [new Assert\Type('string')]);
        }

        $notValid = $violations->count() > 0;
        if ($notValid) {
            $this->addViolations($violations);
        }

        return $notValid;
    }

    private function checkExtensionsAreValid($allowedExtensions): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($allowedExtensions, [
            new Assert\Choice(['choices' => AttributeAllowedExtensions::VALID_EXTENSIONS, 'multiple' => true]),
        ]);

        if ($violations->count() > 0) {
            $this->addViolations($violations);
        }
    }
}
