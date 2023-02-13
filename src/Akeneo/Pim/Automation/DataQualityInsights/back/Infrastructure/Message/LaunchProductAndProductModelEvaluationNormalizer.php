<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Message;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Message\LaunchProductAndProductModelEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private readonly Clock $clock,
    ) {
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, LaunchProductAndProductModelEvaluation::class);

        return [
            'message_created_at' => $object->messageCreatedAt->format('Y-m-d H:i:s'),
            'product_uuids' => $object->productUuids->toArrayString(),
            'product_model_ids' => $object->productModelIds->toArrayString(),
            'criteria' => $object->criteriaToEvaluate,
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof LaunchProductAndProductModelEvaluation;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        Assert::keyExists($data, 'message_created_at');
        Assert::keyExists($data, 'product_uuids');
        Assert::keyExists($data, 'product_model_ids');
        Assert::keyExists($data, 'criteria');

        return new LaunchProductAndProductModelEvaluation(
            $this->clock->fromString($data['message_created_at']),
            ProductUuidCollection::fromStrings($data['product_uuids']),
            ProductModelIdCollection::fromStrings($data['product_model_ids']),
            $data['criteria']
        );
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === LaunchProductAndProductModelEvaluation::class;
    }
}
