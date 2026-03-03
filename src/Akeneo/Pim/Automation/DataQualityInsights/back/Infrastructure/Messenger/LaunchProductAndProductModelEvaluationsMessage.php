<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsMessage
{
    /**
     * @param string[] $criteriaToEvaluate (All criteria will be evaluated if empty)
     */
    public function __construct(
        public readonly \DateTimeImmutable $datetime,
        public readonly ProductUuidCollection $productUuids,
        public readonly ProductModelIdCollection $productModelIds,
        public readonly array $criteriaToEvaluate
    ) {
        Assert::allString($this->criteriaToEvaluate);
        Assert::false(
            $this->productUuids->isEmpty() && $this->productModelIds->isEmpty(),
            'There must be at least one product or product model to evaluate'
        );
    }

    /**
     * @param string[] $criteriaToEvaluate
     */
    public static function forProductsOnly(
        \DateTimeImmutable $datetime,
        ProductUuidCollection $productUuids,
        array $criteriaToEvaluate,
    ): self {
        return new self(
            $datetime,
            $productUuids,
            ProductModelIdCollection::fromStrings([]),
            $criteriaToEvaluate
        );
    }

    /**
     * @param string[] $criteriaToEvaluate
     */
    public static function forProductModelsOnly(
        \DateTimeImmutable $datetime,
        ProductModelIdCollection $productModelIds,
        array $criteriaToEvaluate,
    ): self {
        return new self(
            $datetime,
            ProductUuidCollection::fromStrings([]),
            $productModelIds,
            $criteriaToEvaluate
        );
    }

    public function normalize(): array
    {
        return [
            'datetime' => $this->datetime->format(\DateTimeInterface::ATOM),
            'product_uuids' => $this->productUuids->toArrayString(),
            'product_model_ids' => $this->productModelIds->toArrayString(),
            'criteria' => $this->criteriaToEvaluate,
        ];
    }

    public static function denormalize(array $normalized): LaunchProductAndProductModelEvaluationsMessage
    {
        Assert::keyExists($normalized, 'datetime');
        Assert::string($normalized['datetime']);
        $datetime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalized['datetime'], new \DateTimeZone('UTC'));
        Assert::isInstanceOf($datetime, \DateTimeImmutable::class, sprintf('Failed to create datetime from string "%s"', $normalized['datetime']));

        Assert::keyExists($normalized, 'product_uuids');
        Assert::isArray($normalized['product_uuids']);

        Assert::keyExists($normalized, 'product_model_ids');
        Assert::isArray($normalized['product_model_ids']);

        Assert::keyExists($normalized, 'criteria');
        Assert::isArray($normalized['criteria']);

        return new LaunchProductAndProductModelEvaluationsMessage(
            $datetime,
            ProductUuidCollection::fromStrings($normalized['product_uuids']),
            ProductModelIdCollection::fromStrings($normalized['product_model_ids']),
            $normalized['criteria'],
        );
    }
}
