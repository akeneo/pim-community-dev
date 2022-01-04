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

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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

        if (!$this->isArrayOfValidExtensions($allowedExtensions)) {
            return;
        }

        $this->checkDuplicateExtensions($allowedExtensions);
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

    private function isArrayOfValidExtensions(array $allowedExtensions): bool
    {
        $assertDoesNotContainExtensionSeparator = new Assert\Callback(function (string $allowedExtension, ExecutionContextInterface $context, $payload) {
            if ($this->hasExtensionSeparator($allowedExtension)) {
                $context->buildViolation(AllowedExtensions::MESSAGE_CANNOT_CONTAIN_EXTENSION_SEPARATOR)
                        ->setParameter('%wrong_extension%', $allowedExtension)
                        ->addViolation();
            }
        });
        $assertExtensionOnlyContainsLowercaseLettersOrNumbers = new Assert\Callback(function (string $allowedExtension, ExecutionContextInterface $context, $payload) {
            if ($this->containsForbiddenCharacters($allowedExtension)) {
                $context->buildViolation(AllowedExtensions::MESSAGE_SHOULD_ONLY_CONTAIN_LOWERCASE_LETTERS_AND_NUMBERS)
                        ->setParameter('%wrong_extension%', $allowedExtension)
                        ->addViolation();
            }
        });
        $assertExtensionLengthLowerThanMax = new Assert\Callback(function (string $allowedExtension, ExecutionContextInterface $context, $payload) {
            $actualLength = strlen($allowedExtension);
            if ($actualLength > AttributeAllowedExtensions::MAX_EXTENSION_LENGTH) {
                $context->buildViolation(AllowedExtensions::MESSAGE_CANNOT_BE_LONGER_THAN_MAX)
                        ->setParameter('%actual_length%', (string) strlen($allowedExtension))
                        ->setParameter('%expected_length%', (string) AttributeAllowedExtensions::MAX_EXTENSION_LENGTH)
                        ->addViolation();
            }
        });

        $isValid = true;
        $validator = $this->context->getValidator();
        foreach ($allowedExtensions as $allowedExtension) {
            $violations = $validator->validate($allowedExtension, [
                new Assert\Type('string'),
                $assertDoesNotContainExtensionSeparator,
                $assertExtensionOnlyContainsLowercaseLettersOrNumbers,
                $assertExtensionLengthLowerThanMax
            ]);

            if ($violations->count() > 0) {
                $this->addViolations($violations);
                $isValid = false;
            }
        }

        return $isValid;
    }

    private function hasExtensionSeparator(string $allowedExtension): bool
    {
        return str_starts_with($allowedExtension, AttributeAllowedExtensions::EXTENSION_SEPARATOR);
    }

    private function containsForbiddenCharacters(string $allowedExtension): bool
    {
        preg_match('/[^a-z0-9]/', $allowedExtension, $invalidCharacters);

        return !empty($invalidCharacters);
    }

    private function checkDuplicateExtensions(array $allowedExtensions): void
    {
        $assertThereIsNoDuplicatedExtensions = new Assert\Callback(function (array $allowedExtensions, ExecutionContextInterface $context, $payload) {
            $duplicates = array_diff_assoc($allowedExtensions, array_unique($allowedExtensions));
            if (!empty($duplicates)) {
                $context->buildViolation(AllowedExtensions::MESSAGE_THERE_CANNOT_BE_DUPLICATE_EXTENSIONS)
                        ->setParameter('%duplicates%', implode(', ', $duplicates))
                        ->addViolation();
            }
        });

        $validator = Validation::createValidator();
        $violations = $validator->validate($allowedExtensions, $assertThereIsNoDuplicatedExtensions);

        $notValid = $violations->count() > 0;
        if ($notValid) {
            $this->addViolations($violations);
        }
    }
}
