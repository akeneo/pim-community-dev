<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Webmozart\Assert\Assert;

class NumberData implements ValueDataInterface
{
    /** @var string */
    private $number;

    private function __construct(string $number)
    {
        Assert::stringNotEmpty($number, 'Number data should be a non empty string');

        $this->number = $number;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return $this->number;
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
