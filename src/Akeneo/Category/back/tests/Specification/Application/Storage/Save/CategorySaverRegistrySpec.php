<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\SetCode;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySaverRegistrySpec extends ObjectBehavior
{
    function let(
        CategoryBaseSaver $baseSaver,
        CategoryTranslationsSaver $translationsSaver,
    ) {
        $baseSaver->getSupportedUserIntents()->willReturn([SetCode::class]);
        $translationsSaver->getSupportedUserIntents()->willReturn([SetLabel::class]);

        $this->beConstructedWith([$baseSaver, $translationsSaver]);
    }

    function it_returns_the_saver_related_to_a_user_intent(
        \Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver         $baseSaver,
        \Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver $translationsSaver,
    ) {
        $this->fromUserIntent(SetCode::class)->shouldReturn($baseSaver);
        $this->fromUserIntent(SetLabel::class)->shouldReturn($translationsSaver);
    }

    function it_should_throw_an_exception_when_the_same_user_intent_has_more_than_one_related_saver(
        CategoryBaseSaver                                                         $baseSaver,
        \Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver $translationsSaver,
    )
    {
        $baseSaver->getSupportedUserIntents()->willReturn([SetCode::class]);
        $translationsSaver->getSupportedUserIntents()->willReturn([SetLabel::class, SetCode::class]);

        $this->beConstructedWith([$baseSaver, $translationsSaver]);
        $this->shouldThrow(\LogicException::class)->duringInstantiation();
    }

    function it_should_throw_an_exception_when_the_user_intent_has_no_related_saver(
        \Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver $translationsSaver,
    )
    {
        $translationsSaver->getSupportedUserIntents()->willReturn([SetLabel::class]);

        $this->beConstructedWith([$translationsSaver]);
        $this->shouldThrow(\LogicException::class)->during('fromUserIntent', [SetCode::class]);
    }
}
