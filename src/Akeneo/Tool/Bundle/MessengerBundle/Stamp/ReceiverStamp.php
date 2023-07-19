<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReceiverStamp implements StampInterface
{
    public function __construct(public readonly ReceiverInterface $receiver)
    {
    }
}
