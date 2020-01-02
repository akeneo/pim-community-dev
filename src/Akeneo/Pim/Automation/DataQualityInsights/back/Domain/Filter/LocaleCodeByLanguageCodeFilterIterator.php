<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;

final class LocaleCodeByLanguageCodeFilterIterator extends \FilterIterator
{
    /** @var LanguageCode */
    private $languageCode;

    public function __construct(\Iterator $iterator, LanguageCode $languageCode)
    {
        parent::__construct($iterator);
        $this->languageCode = $languageCode;
    }

    public function accept()
    {
        $localeCode = $this->getInnerIterator()->current();

        $pattern = sprintf(
            '~^%s_[A-Z]{2}$~',
            $this->languageCode->__toString()
        );

        return preg_match($pattern, $localeCode->__toString()) === 1;
    }
}
