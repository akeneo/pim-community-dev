<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;

/**
 * Thrown when a configuration has not been found by a repository.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class ConfigurationNotFound extends \InvalidArgumentException
{
    /** @var string */
    private $configurationCode;

    public function __construct(string $configurationCode, $message = '', $code = 0, \Throwable $previous = null)
    {
        $this->configurationCode = $configurationCode;

        parent::__construct($message, $code, $previous);
    }

    public function configurationCode(): string
    {
        return $this->configurationCode;
    }
}
