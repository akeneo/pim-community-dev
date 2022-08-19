<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\ServiceApi;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ViolationsException extends \LogicException
{
    public function __construct(private ConstraintViolationListInterface $constraintViolationList)
    {
        parent::__construct(
            $this->constraintViolationList instanceof ConstraintViolationList
                ? (string) $this->constraintViolationList
                : 'Some violation(s) are raised'
        );
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
