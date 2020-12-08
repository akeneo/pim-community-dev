<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Test\Acceptance\Channel\InMemoryChannelRepository;
use Akeneo\Test\Acceptance\Channel\InMemoryGetChannelCodeWithLocaleCodes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetChannelCodeWithLocaleCodesSpec extends ObjectBehavior
{
    function let()
    {
        $frFRLocale = new Locale();
        $frFRLocale->setCode('fr_FR');
        $enUSLocale = new Locale();
        $enUSLocale->setCode('en_US');
        $deDELocale = new Locale();
        $deDELocale->setCode('de_DE');

        $ecommerceChannel = new Channel();
        $ecommerceChannel->setCode('ecommerce');
        $ecommerceChannel->addLocale($enUSLocale);
        $ecommerceChannel->addLocale($frFRLocale);

        $mobileChannel = new Channel();
        $mobileChannel->setCode('mobile');
        $mobileChannel->addLocale($enUSLocale);
        $mobileChannel->addLocale($deDELocale);

        $printChannel = new Channel();
        $printChannel->setCode('print');

        $this->beConstructedWith(new InMemoryChannelRepository([
             $ecommerceChannel,
             $mobileChannel,
             $printChannel,
         ]));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetChannelCodeWithLocaleCodes::class);
        $this->shouldImplement(GetChannelCodeWithLocaleCodesInterface::class);
    }

    function it_returns_all_channel_codes_with_bound_locale_codes()
    {
        $this->findAll()->shouldBe([
             [
                 'channelCode' => 'ecommerce',
                 'localeCodes' => ['en_US', 'fr_FR'],
             ],
             [
                 'channelCode' => 'mobile',
                 'localeCodes' => ['en_US', 'de_DE'],
             ],
             [
                 'channelCode' => 'print',
                 'localeCodes' => [],
             ],
         ]);
    }
}
