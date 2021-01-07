<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client\Signature;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SignatureSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Signature::class);
    }

    public function it_creates_a_signature(): void
    {
        $this->createSignature(
            '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b',
            1598777637,
            '{"data":"Hello world!"}'
        )
            ->shouldReturn('9ac9a8cd3e24a416e7001ebd2ca54c76307c058101102070d0b3a5e7e0bf98a6');
    }

    public function it_creates_a_signature_even_if_the_body_is_null(): void
    {
        $this->createSignature(
            '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b',
            1598777637,
            null
        )
            ->shouldReturn('5ec6023d0c34c6af78ce70e06e496a998882d1f4872a4f33245be00560789fb2');
    }
}
