<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

final class LocaleCodeByLanguageCodeFilterIterator extends \FilterIterator
{
    /** @var LanguageCode */
    private $languageCode;

    public function __construct(\Iterator $iterator, LanguageCode $languageCode)
    {
        parent::__construct($iterator);
        $this->languageCode = $languageCode;
    }

    public function accept(): bool
    {
        $localeCode = $this->getInnerIterator()->current();

        $pattern = sprintf(
            '~^%s_[A-Z]{2}$~',
            $this->languageCode->__toString()
        );

        if (preg_match($pattern, $localeCode->__toString()) === 1) {
            return true;
        }

        if ($this->isPortugueseLocale($localeCode) && strval($this->languageCode) === strval($localeCode)) {
            return true;
        }

        return false;
    }

    private function isPortugueseLocale(LocaleCode $localeCode): bool
    {
        return (in_array(strval($localeCode), ['pt_BR']));
    }
}
