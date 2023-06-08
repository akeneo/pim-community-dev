<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\config;

use Akeneo\Tool\Component\Messenger\NormalizableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageTrait;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Message1
{
    public function __construct(public readonly string $text)
    {
    }

    /**
     * @return array<string, string>
     */
    public function normalize(): array
    {
        return ['text' => $this->text];
    }

    /**
     * @return array<string, mixed>
     */
    public static function denormalize(array $normalized): Message1
    {
        Assert::keyExists($normalized, 'text');
        Assert::string($normalized['text']);

        return new Message1($normalized['text']);
    }
}
