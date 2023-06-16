<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Exceptions;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ViolationsException extends \LogicException
{
    public function __construct(private readonly ConstraintViolationListInterface $constraintViolationList)
    {
        parent::__construct(
            $this->constraintViolationList instanceof ConstraintViolationList
                ? (string) $this->constraintViolationList
                : 'Some violation(s) are raised',
        );
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }

    /**
     * @return array<int, array{error: array{code: string|null, message: string}}>
     */
    public function normalize(): array
    {
        if (count($this->constraintViolationList) === 0) {
            return [];
        }

        $constraints = [];
        foreach ($this->constraintViolationList as $constraintViolation) {
            $constraints[] = [
                'error' => [
                    'code' => $constraintViolation->getCode(),
                    'property' => $constraintViolation->getPropertyPath(),
                    'message' => $constraintViolation->getMessage(),
                ],
            ];
        }

        return $constraints;
    }
}
