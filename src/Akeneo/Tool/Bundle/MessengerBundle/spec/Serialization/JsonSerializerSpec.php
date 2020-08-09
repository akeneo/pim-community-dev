<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Serializer;

use Akeneo\Tool\Bundle\MessengerBundle\Serialization\JsonSerializer;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsonSerializerSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer): void
    {
        $this->beConstructedWith([$normalizer]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(JsonSerializer::class);
    }

    public function it_decodes_an_envelope(): void
    {
    }

    public function it_encodes_an_envelope(): void
    {
    }
}
