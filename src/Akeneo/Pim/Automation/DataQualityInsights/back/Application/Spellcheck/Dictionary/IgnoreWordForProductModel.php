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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\ProductModelWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IgnoreWordForProductModel
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private IgnoreWord               $ignoreWord
    ) {
    }

    public function execute(DictionaryWord $word, LocaleCode $localeCode, ProductEntityIdInterface $productId): void
    {
        $this->ignoreWord->execute($word, $localeCode);

        $this->eventDispatcher->dispatch(new ProductModelWordIgnoredEvent($productId));
    }
}
