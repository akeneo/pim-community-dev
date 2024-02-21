<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NativeMessageStamp implements StampInterface
{
    public function __construct(private mixed $nativeMessage)
    {
    }

    public function getNativeMessage(): mixed
    {
        return $this->nativeMessage;
    }
}
