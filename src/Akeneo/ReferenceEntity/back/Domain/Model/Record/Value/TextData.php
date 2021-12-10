<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Webmozart\Assert\Assert;

class TextData implements ValueDataInterface
{
    private function __construct(
        private string $text
    ) {
        Assert::stringNotEmpty($text, 'Text data should be a non empty string');
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return $this->text;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::string($normalizedData, 'Normalized data should be a string');

        return new self($normalizedData);
    }

    public static function fromString(string $string)
    {
        return new self($string);
    }
}
