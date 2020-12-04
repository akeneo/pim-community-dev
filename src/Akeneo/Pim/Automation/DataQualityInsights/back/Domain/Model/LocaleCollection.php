<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

final class LocaleCollection implements \IteratorAggregate, \Countable
{
    /** @var array */
    private $localeCodes;

    public function __construct(array $localeCodes)
    {
        if (count($localeCodes) === 0) {
            throw new \InvalidArgumentException('A locale collection must contain at least one element.');
        }

        foreach ($localeCodes as $localCode) {
            $this->add($localCode);
        }
    }

    public function add(LocaleCode $localeCode): void
    {
        $this->localeCodes[$localeCode->__toString()] = $localeCode;
    }

    public function has(LocaleCode $localeCode): bool
    {
        return isset($this->localeCodes[$localeCode->__toString()]);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->localeCodes);
    }

    public function count(): int
    {
        return count($this->localeCodes);
    }

    public function toArrayString(): array
    {
        return array_values(
            array_map(fn (LocaleCode $localeCode) => $localeCode->__toString(), $this->localeCodes)
        );
    }
}
