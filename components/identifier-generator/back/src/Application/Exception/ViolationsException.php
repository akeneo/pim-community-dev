<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ViolationsException extends \LogicException
{
    public function __construct(private ErrorList $constraintViolationList)
    {
        parent::__construct($this->constraintViolationList->getMergedMessages());
    }

    public function violations(): ErrorList
    {
        return $this->constraintViolationList;
    }

    public function normalize(): array
    {
        return array_map(fn (Error $error): array => [
                'path' => $error->getPath(),
                'parameters' => $error->getParameters(),
                'message' => $error->getMessage(),
            ],
            $this->constraintViolationList->getErrors()
        );
    }
}
