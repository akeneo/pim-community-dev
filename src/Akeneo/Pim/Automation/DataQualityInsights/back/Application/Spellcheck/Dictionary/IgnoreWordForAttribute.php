<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\AttributeWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IgnoreWordForAttribute
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var IgnoreWord */
    private $ignoreWord;

    public function __construct(EventDispatcherInterface $eventDispatcher, IgnoreWord $ignoreWord)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->ignoreWord = $ignoreWord;
    }

    public function execute(DictionaryWord $word, LocaleCode $localeCode, ?AttributeCode $attributeCode): void
    {
        $this->ignoreWord->execute($word, $localeCode);

        if ($attributeCode !== null) {
            $this->eventDispatcher->dispatch(new AttributeWordIgnoredEvent($attributeCode));
        }
    }
}
