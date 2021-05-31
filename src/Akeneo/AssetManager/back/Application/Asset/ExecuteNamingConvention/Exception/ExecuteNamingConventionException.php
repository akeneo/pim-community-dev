<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception;

final class ExecuteNamingConventionException extends AbstractExecuteNamingConventionException
{
    public function __construct(
        \Throwable $previous = null
    ) {
        parent::__construct(
            $previous !== null ? $previous->getMessage() : 'Unexpected error during naming convention execution',
            0,
            $previous
        );
    }
}
