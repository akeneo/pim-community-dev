<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Tool\Component\Messenger\NormalizableMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
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

    public function it_is_a_traceable_message(): void
    {
        $this->shouldImplement(TraceableMessageInterface::class);
    }

    public function it_is_a_normalizable_message(): void
    {
        $this->shouldImplement(NormalizableMessageInterface::class);
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
