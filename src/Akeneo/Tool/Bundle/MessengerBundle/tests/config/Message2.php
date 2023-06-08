<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\config;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Message2
{
    public function __construct(public readonly int $number)
    {
    }

    /**
     * @return array<string, int>
     */
    public function normalize(): array
    {
        return ['number' => $this->number];
    }

    /**
     * @return array<string, mixed>
     */
    public static function denormalize(array $normalized): Message2
    {
        Assert::keyExists($normalized, 'number');
        Assert::integer($normalized['number']);

        return new Message2($normalized['number']);
    }
}
