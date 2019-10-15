<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model\Write;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLabel
{
    private $label;

    private function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function create(string $label): self
    {
        if (strlen($label) > 100) {
            throw new \InvalidArgumentException('Label cannot be longer than 100 characters');
        }

        return new self($label);
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
