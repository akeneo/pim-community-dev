<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright {2019} Akeneo SAS (http://www.akeneo.com)
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
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
