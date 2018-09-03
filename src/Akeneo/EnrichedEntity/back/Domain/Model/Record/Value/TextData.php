<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Webmozart\Assert\Assert;

class TextData implements ValueDataInterface
{
    /** @var string */
    private $text;

    public function __construct(string $text)
    {
        Assert::notEmpty($text, 'Text data should be a non empty string');

        $this->text = $text;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return (string) $this->text;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::string($normalizedData, 'Normalized data should be a string');

        return new self($normalizedData);
    }

    public static function createFromString(string $string)
    {
        return new self($string);
    }
}
