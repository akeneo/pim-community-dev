<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ViolationsException extends \LogicException
{
    public function __construct(private ErrorList $constraintViolationList)
    {
        parent::__construct(
            $this->constraintViolationList instanceof ErrorList
                ? $this->constraintViolationList->getAllMessages()
                : 'Some violation(s) are raised'
        );
    }

    public function violations(): ErrorList
    {
        return $this->constraintViolationList;
    }
}
