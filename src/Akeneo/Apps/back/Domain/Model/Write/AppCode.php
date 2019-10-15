<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model\Write;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppCode
{
    private $code;

    private function __construct(string $code)
    {
        $this->code = $code;
    }

    public static function create(string $code): self
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('Code is required');
        }
        if (strlen($code) > 100) {
            throw new \InvalidArgumentException('Code cannot be longer than 100 characters');
        }
        if (!preg_match('/^[0-9a-zA-Z_]+$/', $code)) {
            throw new \InvalidArgumentException('Code can only contain alphanumeric characters and underscore');
        }

        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
