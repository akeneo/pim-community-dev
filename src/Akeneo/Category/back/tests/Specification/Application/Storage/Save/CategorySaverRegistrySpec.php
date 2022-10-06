<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
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
        $baseSaver->getSupportedUserIntents()->willReturn([
            SetRichText::class,
            SetText::class,
            SetTextArea::class,
            SetImage::class,
        ]);

        $translationsSaver->getSupportedUserIntents()->willReturn([
            SetLabel::class
        ]);

        $this->beConstructedWith([$baseSaver, $translationsSaver]);
    }

    function it_returns_the_saver_related_to_a_user_intent(
        CategoryBaseSaver $baseSaver,
        CategoryTranslationsSaver $translationsSaver,
    ) {
        $this->fromUserIntent(SetText::class)->shouldReturn($baseSaver);
        $this->fromUserIntent(SetTextArea::class)->shouldReturn($baseSaver);
        $this->fromUserIntent(SetRichText::class)->shouldReturn($baseSaver);
        $this->fromUserIntent(SetImage::class)->shouldReturn($baseSaver);

        $this->fromUserIntent(SetLabel::class)->shouldReturn($translationsSaver);
    }

    function it_should_throw_an_exception_when_the_same_user_intent_has_more_than_one_related_saver(
        CategoryBaseSaver $baseSaver,
        CategoryTranslationsSaver $translationsSaver,
    ) {
        $baseSaver->getSupportedUserIntents()->willReturn([
            SetText::class,
            SetTextArea::class,
            SetRichText::class,
            SetImage::class,
        ]);
        $translationsSaver->getSupportedUserIntents()->willReturn([SetLabel::class, SetText::class]);

        $this->beConstructedWith([$baseSaver, $translationsSaver]);
        $this->shouldThrow(\LogicException::class)->duringInstantiation();
    }

    function it_should_throw_an_exception_when_the_user_intent_has_no_related_saver(
        CategoryTranslationsSaver $translationsSaver,
    ) {
        $translationsSaver->getSupportedUserIntents()->willReturn([SetLabel::class]);

        $this->beConstructedWith([$translationsSaver]);
        $this->shouldThrow(\LogicException::class)->during('fromUserIntent', [SetText::class]);
    }
}
