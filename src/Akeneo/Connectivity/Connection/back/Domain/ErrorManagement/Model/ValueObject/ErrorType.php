<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ErrorType implements \Stringable
{
    private string $type;

    public function __construct(string $type)
    {
        if (!\in_array($type, ErrorTypes::getAll())) {
            throw new \InvalidArgumentException(
                \sprintf('The given error type "%s" is unknown.', $type)
            );
        }
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
