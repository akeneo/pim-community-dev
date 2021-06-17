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

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute\MediaLink;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;
use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PrefixValidator extends ConstraintValidator
{
    /** @var string[] */
    private array $allowedProtocols;

    public function __construct(array $allowedProtocols)
    {
        Assert::allString($allowedProtocols);
        $this->allowedProtocols = $allowedProtocols;
    }

    public function validate($prefix, Constraint $constraint)
    {
        if (!$constraint instanceof Prefix) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($prefix, [
            new Constraints\Type('string'),
        ]);

        if ('' === $prefix) {
            $this->context->buildViolation(Prefix::MESSAGE_NOT_EMPTY_STRING)
                ->addViolation();
        }

        if (is_string($prefix)) {
            $this->validateProtocol($prefix);
        }

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }
    }

    private function validateProtocol(string $prefix): void
    {
        if ($this->containsANonAllowedProtocol($prefix)) {
            $this->context->buildViolation(Prefix::PROTOCOL_NOT_ALLOWED)
                ->setParameter('%allowed_protocols%', implode(', ', $this->allowedProtocols))
                ->addViolation();
        }
    }

    private function containsANonAllowedProtocol(string $url): bool
    {
        $urlParts = \explode('://', $url);
        if (count($urlParts) < 2) {
            return false; // Relative urls are authorized.
        }

        return !\in_array(\strtolower($urlParts[0]), $this->allowedProtocols);
    }
}
