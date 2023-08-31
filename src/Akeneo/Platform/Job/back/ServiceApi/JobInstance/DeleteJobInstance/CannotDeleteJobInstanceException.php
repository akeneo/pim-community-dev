<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance;

class CannotDeleteJobInstanceException extends \RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function notFound(string $code): self
    {
        return new self(sprintf('Job instance with the code "%s" does not exist', $code));
    }

    public static function insufficientPrivilege(): self
    {
        return new self('Insufficient privilege');
    }
}
