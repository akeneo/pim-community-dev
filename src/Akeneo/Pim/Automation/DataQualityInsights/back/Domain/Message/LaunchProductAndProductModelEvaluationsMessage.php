<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Message;

use Akeneo\Pim\Platform\Messaging\Domain\SerializableMessageInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsMessage implements SerializableMessageInterface
{
    public function __construct(public readonly string $text)
    {
    }

    public function normalize(): array
    {
        return ['text' => $this->text];
    }

    public static function denormalize(array $normalized): SerializableMessageInterface
    {
        Assert::keyExists($normalized, 'text');
        Assert::string($normalized['text']);

        return new LaunchProductAndProductModelEvaluationsMessage($normalized['text']);
    }
}
