<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationListException extends \InvalidArgumentException
{
    public const MESSAGE = 'akeneo_connectivity.connection.constraint_violation_list_exception';

    /**
     * @param ConstraintViolationListInterface<ConstraintViolationInterface> $constraintViolationList
     */
    public function __construct(private ConstraintViolationListInterface $constraintViolationList)
    {
        parent::__construct(self::MESSAGE);
    }

    /**
     * @return ConstraintViolationListInterface<ConstraintViolationInterface>
     */
    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
