<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Message;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Message\LaunchProductAndProductModelEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Message\LaunchProductAndProductModelEvaluationNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaunchProductAndProductModelEvaluationNormalizerSpec extends ObjectBehavior
{
    public function let(Clock $clock)
    {
        $this->beConstructedWith($clock);
    }

    public function it_is_a_normalizer_and_a_denormalizer(): void
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
        $this->shouldHaveType(LaunchProductAndProductModelEvaluationNormalizer::class);
    }

    public function it_supports_normalization_on_launch_product_and_product_model_evaluation_messages(): void
    {
        $this->supportsNormalization(new LaunchProductAndProductModelEvaluation(
            new \DateTimeImmutable(),
            ProductUuidCollection::fromStrings(['6d125b99-d971-41d9-a264-b020cd486aee', 'fef37e64-a963-47a9-b087-2cc67968f0a2']),
            ProductModelIdCollection::fromStrings([]),
            []
        ))->shouldReturn(true);

        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    public function it_supports_denormalization_on_launch_product_and_product_model_evaluation_messages(): void
    {
        $this->supportsDenormalization([], LaunchProductAndProductModelEvaluation::class)->shouldReturn(true);
        $this->supportsDenormalization([], 'NotSupported\Message')->shouldReturn(false);
    }

    public function it_normalizes_a_launch_product_and_product_model_evaluation_message(): void
    {
        $message = new LaunchProductAndProductModelEvaluation(
            new \DateTimeImmutable('2023-02-13 11:34:42'),
            ProductUuidCollection::fromStrings(['6d125b99-d971-41d9-a264-b020cd486aee', 'fef37e64-a963-47a9-b087-2cc67968f0a2']),
            ProductModelIdCollection::fromStrings(['42']),
            ['attribute_spelling']
        );

        $this->normalize($message)->shouldBeLike([
            'message_created_at' => '2023-02-13 11:34:42',
            'product_uuids' => ['6d125b99-d971-41d9-a264-b020cd486aee', 'fef37e64-a963-47a9-b087-2cc67968f0a2'],
            'product_model_ids' => ['42'],
            'criteria' => ['attribute_spelling'],
        ]);
    }

    public function it_denormalizes_a_launch_product_and_product_model_evaluation_message(Clock $clock): void
    {
        $dateTime = new \DateTimeImmutable('2023-02-13 11:34:42');
        $clock->fromString('2023-02-13 11:34:42')->willReturn($dateTime);

        $normalizedMessage = [
            'message_created_at' => '2023-02-13 11:34:42',
            'product_uuids' => ['6d125b99-d971-41d9-a264-b020cd486aee', 'fef37e64-a963-47a9-b087-2cc67968f0a2'],
            'product_model_ids' => ['42'],
            'criteria' => ['attribute_spelling'],
        ];

        $message = new LaunchProductAndProductModelEvaluation(
            $dateTime,
            ProductUuidCollection::fromStrings(['6d125b99-d971-41d9-a264-b020cd486aee', 'fef37e64-a963-47a9-b087-2cc67968f0a2']),
            ProductModelIdCollection::fromStrings(['42']),
            ['attribute_spelling']
        );

        $this->denormalize($normalizedMessage, LaunchProductAndProductModelEvaluation::class)->shouldBeLike($message);
    }
}
