<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLabel
{
    private const CONSTRAINT_KEY = 'akeneo_apps.app.constraint.label.%s';
    private $label;

    public function __construct(string $label)
    {
        $label = trim($label);

        if (empty($label)) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'required'));
        }
        if (strlen($label) > 100) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'too_long'));
        }

        $this->label = $label;
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
