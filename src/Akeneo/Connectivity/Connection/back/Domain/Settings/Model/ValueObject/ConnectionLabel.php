<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionLabel
{
    private string $label;

    public function __construct(string $label)
    {
        $label = \trim($label);

        if (empty($label)) {
            throw new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.required');
        }
        if (\mb_strlen($label) < 3) {
            throw new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.too_short');
        }
        if (\mb_strlen($label) > 100) {
            throw new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.too_long');
        }

        $this->label = $label;
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
