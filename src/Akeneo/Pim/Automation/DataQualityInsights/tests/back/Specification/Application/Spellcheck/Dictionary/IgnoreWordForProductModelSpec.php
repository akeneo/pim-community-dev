<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary\IgnoreWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductModelWordIgnoredEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IgnoreWordForProductModelSpec extends ObjectBehavior
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
        $productId = new ProductId(1234);

        $ignoreWord->execute($word, $locale)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ProductModelWordIgnoredEvent::class))->shouldBeCalled(true);

        $this->execute($word, $locale, $productId);
    }
}
