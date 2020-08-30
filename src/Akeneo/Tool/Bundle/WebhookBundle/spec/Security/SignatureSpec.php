<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\WebhookBundle\Security;

use Akeneo\Tool\Bundle\WebhookBundle\Security\Signature;
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
        $this->create(
            '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b',
            '{"data":"Hello world!"}',
            1598777637
        )
            ->shouldReturn('9ac9a8cd3e24a416e7001ebd2ca54c76307c058101102070d0b3a5e7e0bf98a6');
    }

    public function it_verify_a_valid_signature(): void
    {
        $signature = Signature::create(
            '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b',
            '{"data":"Hello world!"}',
            1598777637
        );

        $this->verify('9ac9a8cd3e24a416e7001ebd2ca54c76307c058101102070d0b3a5e7e0bf98a6', $signature)
            ->shouldReturn(true);
    }

    public function it_verify_an_invalid_signature(): void
    {
        $signature = Signature::create(
            '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b',
            '{"data":"Hello world!"}',
            1598777637
        );

        $this->verify('wrong_signature', $signature)
            ->shouldReturn(false);
    }
}
