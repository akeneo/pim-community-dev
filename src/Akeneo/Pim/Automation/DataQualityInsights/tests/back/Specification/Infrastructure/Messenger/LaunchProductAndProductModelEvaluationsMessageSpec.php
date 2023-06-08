<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsMessageSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            new \DateTimeImmutable(),
            ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed']),
            ProductModelIdCollection::fromStrings([]),
            []
        );
    }

    public function it_can_be_created_for_products_only(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));
        $productUuids = ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de']);

        $this->beConstructedThrough('forProductsOnly', [$datetime, $productUuids, []]);

        $this->productUuids->shouldBe($productUuids);
        $this->productModelIds->isEmpty()->shouldReturn(true);
    }

    public function it_can_be_created_for_product_models_only(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));
        $productModelIds = ProductModelIdCollection::fromStrings(['42', '123']);

        $this->beConstructedThrough('forProductModelsOnly', [$datetime, $productModelIds, []]);

        $this->productModelIds->shouldBe($productModelIds);
        $this->productUuids->isEmpty()->shouldReturn(true);
    }

    public function it_normalizes_itself(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));

        $this->beConstructedWith(
            $datetime,
            ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de']),
            ProductModelIdCollection::fromStrings(['42', '123']),
            ['consistency_spelling']
        );

        $this->normalize()->shouldReturn([
            'datetime' => $datetime->format(\DateTimeInterface::ATOM),
            'product_uuids' => ['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de'],
            'product_model_ids' => ['42', '123'],
            'criteria' => ['consistency_spelling'],
        ]);
    }

    public function it_denormalizes_itself(): void
    {
        $datetime = new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC'));

        $message = LaunchProductAndProductModelEvaluationsMessage::denormalize([
            'datetime' => $datetime->format(\DateTimeInterface::ATOM),
            'product_uuids' => ['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de'],
            'product_model_ids' => ['42', '123'],
            'criteria' => ['consistency_spelling'],
        ]);

        Assert::eq(new LaunchProductAndProductModelEvaluationsMessage(
            $datetime,
            ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed', 'fd470d52-7723-4890-85a0-e79be625e2de']),
            ProductModelIdCollection::fromStrings(['42', '123']),
            ['consistency_spelling']
        ), $message);
    }

    public function it_throws_an_exception_if_there_is_nothing_to_evaluate(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC')),
            ProductUuidCollection::fromStrings([]),
            ProductModelIdCollection::fromStrings([]),
            ['consistency_spelling']
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_a_criteria_to_evaluate_has_invalid_type(): void
    {
        $this->beConstructedWith(
            new \DateTimeImmutable('2023-02-12 11:34:32', new \DateTimeZone('UTC')),
            ProductUuidCollection::fromStrings([]),
            ProductModelIdCollection::fromStrings(['42', '123']),
            ['consistency_spelling', 1234]
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
