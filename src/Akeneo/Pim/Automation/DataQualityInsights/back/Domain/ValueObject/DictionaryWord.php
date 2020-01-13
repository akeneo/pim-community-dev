<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;


final class DictionaryWord
{
    /** @var string */
    private $word;

    public function __construct(string $word)
    {
        if(preg_match('~^[a-zA-Z]+$~', $word) !== 1)
        {
            throw new \InvalidArgumentException('A word must be contain only alphabetical characters.');
        }

        $this->word = $word;
    }

    public function __toString()
    {
        return $this->word;
    }
}
