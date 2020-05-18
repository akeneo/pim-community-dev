<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;

interface DictionarySource
{
    public function getDictionary(LocaleCollection $localeCollection): Dictionary;
}
