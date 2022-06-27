<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary\IgnoreWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductWordIgnoredEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IgnoreWordForProductSpec extends ObjectBehavior
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
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));

        $ignoreWord->execute($word, $locale)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ProductWordIgnoredEvent::class))->shouldBeCalled(true);

        $this->execute($word, $locale, $productUuid);
    }
}
