<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryChannelsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'ecommerce' => 1,
            'mobile' => 2,
        ]);
    }

    public function it_gets_a_channel_id_from_its_code(): void
    {
        $this->getIdByCode('mobile')->shouldReturn(2);
    }

    public function it_gets_a_channel_code_from_its_id(): void
    {
        $this->getCodeById(2)->shouldReturn('mobile');
    }

    public function it_returns_null_if_the_channel_does_not_exist(): void
    {
        $this->getIdByCode('print')->shouldReturn(null);
        $this->getCodeById(42)->shouldReturn(null);
    }
}
