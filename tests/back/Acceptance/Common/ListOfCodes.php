<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Common;

/**
 * Manage a list of code like 'EUR, USD' given to gherkins step
 */
class ListOfCodes
{
    /** @var string */
    private $listOfCode;

    public function __construct(string $listOfCode)
    {
        $this->listOfCode = $listOfCode;
    }

    /**
     * @param string $separator
     *
     * @return array
     */
    public function explode(string $separator = ','): array
    {
        $codes = explode($separator, $this->listOfCode);

        return array_map('trim', $codes);
    }
}
