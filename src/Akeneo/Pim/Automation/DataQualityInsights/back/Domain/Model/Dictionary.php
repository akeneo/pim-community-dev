<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

final class Dictionary implements \IteratorAggregate, \Countable
{
    /** @var array */
    private $words = [];

    public function __construct(?array $words = [])
    {
        foreach ($words as $word) {
            $this->add($word);
        }
    }

    public function add(string $word): void
    {
        $this->words[$word] = $word;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->words);
    }

    public function count(): int
    {
        return count($this->words);
    }
}
