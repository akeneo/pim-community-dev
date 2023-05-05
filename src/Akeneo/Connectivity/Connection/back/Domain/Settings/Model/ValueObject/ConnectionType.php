<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectionType implements \Stringable
{
    public const DEFAULT_TYPE = 'default';
    public const APP_TYPE = 'app';

    private string $type;

    public function __construct(?string $type = self::DEFAULT_TYPE)
    {
        $type ??= self::DEFAULT_TYPE;

        if (empty($type)) {
            throw new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.type.required');
        }

        if (\mb_strlen($type) > 30) {
            throw new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.type.too_long');
        }

        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
