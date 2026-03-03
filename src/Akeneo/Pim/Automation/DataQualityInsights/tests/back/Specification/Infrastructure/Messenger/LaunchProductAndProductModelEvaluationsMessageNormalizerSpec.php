<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsMessageNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_normalizer_and_denormalizer(): void
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
    }

    public function it_supports_denormalization_of_launch_product_and_product_model_evaluations_message(): void
    {
        $this->supportsDenormalization([], LaunchProductAndProductModelEvaluationsMessage::class)->shouldReturn(true);
    }

    public function it_supports_normalization_of_launch_product_and_product_model_evaluations_message(): void
    {
        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable(),
            ProductUuidCollection::fromProductUuids([
                ProductUuid::fromUuid(Uuid::uuid4()),
            ]),
            ProductModelIdCollection::fromProductModelIds([]),
            []
        );

        $this->supportsNormalization($message)->shouldReturn(true);
    }

    public function it_throws_an_exception_if_the_object_to_normalize_is_not_supported(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('normalize', [new \stdClass()]);
    }
}
