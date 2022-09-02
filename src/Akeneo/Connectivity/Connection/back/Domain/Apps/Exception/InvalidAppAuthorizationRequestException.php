<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidAppAuthorizationRequestException extends \ErrorException
{
    /** @var ConstraintViolationListInterface<ConstraintViolationInterface> */
    private ConstraintViolationListInterface $constraintViolationList;

    /**
     * @param ConstraintViolationListInterface<ConstraintViolationInterface> $constraintViolationList
     */
    public function __construct(ConstraintViolationListInterface $constraintViolationList)
    {
        $message = \count($constraintViolationList) > 0 ? (string) $constraintViolationList->get(0)->getMessage() : '';

        parent::__construct($message);

        $this->constraintViolationList = $constraintViolationList;
    }

    /**
     * @return ConstraintViolationListInterface<ConstraintViolationInterface>
     */
    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
