<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class DictionaryWord
{
    /** @var string */
    private $word;

    public function __construct(string $word)
    {
        $anyKindOfLetterFromAnyLanguageRegex = "~^[\p{L}'-]+$~u";
        if (preg_match($anyKindOfLetterFromAnyLanguageRegex, $word) !== 1) {
            throw new \InvalidArgumentException('A word must contain only letters.');
        }

        $this->word = $word;
    }

    public function __toString()
    {
        return $this->word;
    }
}
