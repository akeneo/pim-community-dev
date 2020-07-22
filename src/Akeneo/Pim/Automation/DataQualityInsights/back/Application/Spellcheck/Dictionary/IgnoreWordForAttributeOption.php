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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\AttributeOptionWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IgnoreWordForAttributeOption
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

    public function execute(DictionaryWord $word, LocaleCode $localeCode, ?AttributeOptionCode $attributeOptionCode): void
    {
        $this->ignoreWord->execute($word, $localeCode);

        if ($attributeOptionCode !== null) {
            $this->eventDispatcher->dispatch(new AttributeOptionWordIgnoredEvent($attributeOptionCode));
        }
    }
}
