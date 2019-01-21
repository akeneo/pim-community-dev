<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\CertificateExpirationDate;
use PhpSpec\ObjectBehavior;

class CertificateExpirationDateSpec extends ObjectBehavior
{
    private const VALID_DATE = 'Wed, 13 Sep 2028 09:30:13 +0000';

    function it_is_initializable()
    {
        $this->beConstructedWith(self::VALID_DATE);
        $this->shouldHaveType(CertificateExpirationDate::class);
    }

    function it_throws_an_exception_if_the_date_is_empty()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_if_the_date_is_malformed()
    {
        $this->beConstructedWith('jambon');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_tell_if_the_date_expires_soon()
    {
        $this->beConstructedWith(self::VALID_DATE);

        $this->doesExpireInLessThanDays(
            new \DateTimeImmutable('Mon, 21 Jan 2019 14:51:00 +0000'),
            30
        )->shouldReturn(false);

        $this->doesExpireInLessThanDays(
            new \DateTimeImmutable('Tue, 15 Aug 2028 09:30:13 +0000'),
            30
        )->shouldReturn(true);
    }
}
