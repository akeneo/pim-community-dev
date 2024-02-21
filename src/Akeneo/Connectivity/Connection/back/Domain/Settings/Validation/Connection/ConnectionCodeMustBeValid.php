<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ConnectionCodeMustBeValid
{
    public static function validate(string $connectionCode, ExecutionContextInterface $context): void
    {
        try {
            new ConnectionCode($connectionCode);
        } catch (\InvalidArgumentException $e) {
            $context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
