<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Webmozart\Assert\Assert;

class TextData implements ValueDataInterface
{
    private string $text;

    private function __construct(string $text)
    {
        Assert::stringNotEmpty($text, 'Text data should be a non empty string');

        $this->text = $text;
    }

    public function equals(ValueDataInterface $valueData): bool
    {
        return $valueData instanceof self && $valueData->normalize() === $this->normalize();
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
