<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Infrastructure\Component\Model;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelTranslation;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Channel::class);
    }

    public function it_gets_a_translation_even_if_the_locale_case_is_wrong(
        ChannelTranslation $translationEn,
    )
    {
        $translationEn->getLocale()->willReturn('EN_US');
        $this->addTranslation($translationEn);

        $this->getTranslation('en_US')->shouldReturn($translationEn);
    }
}
