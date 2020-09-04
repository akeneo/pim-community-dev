<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\BusinessEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEventNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BusinessEventNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(BusinessEventNormalizer::class);
    }

    public function it_supports_normalization_for_all_class(): void
    {
        $this->supportsNormalization(Argument::any())->shouldReturn(true);
    }

    public function it_supports_denormalization_for_businnes_event(): void
    {
        $businessEvent = new MyBusinessEvent();

        $this->supportsDenormalization([], $businessEvent)->shouldReturn(true);
    }

    public function it_supports_denormalization_only_for_businnes_event(): void
    {
        $notABusinessEvent = new \stdClass();

        $this->supportsDenormalization([], $notABusinessEvent)->shouldReturn(false);
    }

    public function it_normalizes()
    {
        $businessEvent = new MyBusinessEvent();

        $expected = [
            'name' => 'my_business_event',
            'author' => 'magento_connection',
            'data' => [],
            'timestamp' => 123456,
            'uuid' => 'a1603650-e1a7-4e66-8251-87f93c500087',
        ];

        $this->normalize($businessEvent)->shouldReturn($expected);
    }

    public function it_denormalizes()
    {
        $data = [
            'name' => 'my_business_event',
            'author' => 'magento_connection',
            'data' => [],
            'timestamp' => 123456,
            'uuid' => 'a1603650-e1a7-4e66-8251-87f93c500087',
        ];

        $this->denormalize($data, MyBusinessEvent::class)->shouldReturnAnInstanceOf(MyBusinessEvent::class);
    }
}

class MyBusinessEvent extends BusinessEvent
{
    public function __construct(
        string $author = 'magento_connection',
        array $data = [],
        ?int $timestamp = 123456,
        ?string $uuid = 'a1603650-e1a7-4e66-8251-87f93c500087'
    ) {
        parent::__construct('my_business_event', $author, $data, $timestamp, $uuid);
    }
}
