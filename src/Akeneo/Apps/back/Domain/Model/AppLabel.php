<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLabel
{
    private $label;

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function create(string $label): self
    {
        if (strlen($label) > 100) {
            throw new \Exception('Label is too long');
        }
        // TODO: Validation + Id Generation

        return new self($label);
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
