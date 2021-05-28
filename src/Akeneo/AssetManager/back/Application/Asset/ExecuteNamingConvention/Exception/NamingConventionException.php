<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception;

/**
 * This exception represents an error thrown during the naming convention execution.
 * As the configuration of naming convention contains a true/false "abort_on_error", the behavior of the exception
 * handling can change according to this configuration.
 * This exception contains the real exception ($embedException) and an information about what to do
 * with it ($namingConventionAbortOnError).
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class NamingConventionException extends \Exception
{
    private \Exception $embeddedException;

    private bool $namingConventionAbortOnError;

    public function __construct(\Exception $exception, bool $namingConventionAbortOnError)
    {
        parent::__construct($exception->getMessage(), $exception->getCode());
        $this->embeddedException = $exception;
        $this->namingConventionAbortOnError = $namingConventionAbortOnError;
    }

    public function getEmbeddedException(): \Exception
    {
        return $this->embeddedException;
    }

    public function namingConventionAbortOnError(): bool
    {
        return $this->namingConventionAbortOnError;
    }
}
