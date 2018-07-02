<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue;

final class ListOfCodes
{
    /** @var array */
    private $arrayOfCodes;

    /**
     * ListOfCodes constructor.
     *
     * @param array $arrayOfCodes
     *
     * @throws \InvalidArgumentException
     */
    private function __construct(array $arrayOfCodes)
    {
        foreach ($arrayOfCodes as $code) {
            if (!$code instanceOf Code) {
                throw new \InvalidArgumentException('You must provider a list of Code object');
            }
        }

        $this->arrayOfCodes = $arrayOfCodes;
    }

    /**
     * @param array $arrayOfCodes
     *
     * @return ListOfCodes
     *
     * @throws \InvalidArgumentException
     */
    public static function fromArrayOfString(array $arrayOfCodes): ListOfCodes
    {
        $codes = [];
        foreach ($arrayOfCodes as $category) {
            $codes[] = Code::fromString($category);
        }

        return new self($codes);
    }

    /**
     * @return ListOfCodes
     */
    public static function initialize(): ListOfCodes
    {
        return new self([]);
    }

    /**
     * @return array
     */
    public function toStandardFormat(): array
    {
        return \array_map(function (Code $code) {
            return $code->toStandardFormat();
        }, $this->arrayOfCodes);
    }
}
