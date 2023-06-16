<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ValueObject;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Url implements \Stringable
{
    public function __construct(private string $url)
    {
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
