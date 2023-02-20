<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command\Exceptions;

use Symfony\Component\Validator\ConstraintViolation;
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
                ? $this->normalize()
                : 'Some violation(s) are raised',
        );
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }

    public function normalize(): string
    {
        if (count($this->constraintViolationList) === 0) {
            return '';
        }

        $constraints = [];
        foreach ($this->constraintViolationList as $constraintViolation) {
            $constraints[] = $constraintViolation->getMessage();
        }

        return implode("\n", $constraints);
    }
}
