<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CommandValidatorInterface
{
    /**
     * @throws ViolationsException
     */
    public function validate(CommandInterface $command): void;
}
