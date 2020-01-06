<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionCode
{
    private const CONSTRAINT_KEY = 'akeneo_connectivity.connection.connection.constraint.code.%s';
    private $code;

    public function __construct(string $code)
    {
        $code = trim($code);

        if (empty($code)) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'required'));
        }
        if (mb_strlen($code) < 3) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'too_short'));
        }
        if (mb_strlen($code) > 100) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'too_long'));
        }
        if (!preg_match('/^[0-9a-zA-Z_]+$/', $code)) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'invalid'));
        }

        $this->code = $code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
