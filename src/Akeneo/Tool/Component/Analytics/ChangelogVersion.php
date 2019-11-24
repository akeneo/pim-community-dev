<?php

namespace Akeneo\Tool\Component\Analytics;

use DateTimeImmutable;

class ChangelogVersion
{
    /** @var string */
    public $name;
    /** @var DateTimeImmutable */
    public $date;
    /** @var string[] */
    private $changes = [];

    public function __construct(string $name, string $date)
    {
        $this->name = $name;
        if (!empty($date)) {
            $this->date = new DateTimeImmutable($date);
        }
    }

    public function addChange(string $line, string $sectionName): void
    {
        if (!array_key_exists($sectionName, $this->changes)) {
            $this->changes[$sectionName] = [];
        }
        $this->changes[$sectionName][] = trim($line);
    }

    /**
     * @return string[]
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    public function normalize(): array
    {
        return [
            'name' => $this->name,
            'date' => $this->date ? $this->date->format('Y-m-d') : '',
            'changes' => $this->changes
        ];
    }
}
