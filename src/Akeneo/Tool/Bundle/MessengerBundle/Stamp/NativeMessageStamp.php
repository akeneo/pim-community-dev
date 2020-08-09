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
    /** @var mixed */
    private $nativeMessage;

    /**
     * @var mixed $nativeMessage
     */
    public function __construct($nativeMessage)
    {
        $this->nativeMessage = $nativeMessage;
    }

    /**
     * @return mixed
     */
    public function getNativeMessage()
    {
        return $this->nativeMessage;
    }
}
