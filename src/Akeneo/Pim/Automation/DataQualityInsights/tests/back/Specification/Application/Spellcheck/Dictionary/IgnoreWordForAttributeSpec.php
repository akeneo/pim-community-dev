<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary\IgnoreWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\AttributeWordIgnoredEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IgnoreWordForAttributeSpec extends ObjectBehavior
{
    public function let(
        EventDispatcherInterface $eventDispatcher,
        IgnoreWord $ignoreWord
    ) {
        $this->beConstructedWith($eventDispatcher, $ignoreWord);
    }

    public function it_ignores_word_and_dispatches_event(
        $ignoreWord,
        $eventDispatcher
    ) {
        $word = new DictionaryWord('anyword');
        $locale = new LocaleCode('en_US');
        $attribute = new AttributeCode('attribute_code');

        $ignoreWord->execute($word, $locale)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(AttributeWordIgnoredEvent::class))->shouldBeCalled();

        $this->execute($word, $locale, $attribute);
    }

    public function it_ignores_word_and_does_not_dispatch_event_when_attribute_is_null(
        $ignoreWord,
        $eventDispatcher
    ) {
        $word = new DictionaryWord('anyword');
        $locale = new LocaleCode('en_US');
        $attribute = null;

        $ignoreWord->execute($word, $locale)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(AttributeWordIgnoredEvent::class))->shouldNotBeCalled();

        $this->execute($word, $locale, $attribute);
    }
}
