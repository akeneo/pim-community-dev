<?php

namespace Akeneo\Tool\Component\Analytics;

class Changelog
{
    const PATTERN_VERSION = '/^# (\d.\d.\S+)(?: \((.+)\))?/';
    const PATTERN_SECTION = '/^## (.+)$/';

    /** @var array ChangelogVersion[] */
    private $versions = [];
    /** @var ChangelogVersion */
    private $currentVersion;
    /** @var string */
    private $currentSection = '';

    public function parseFile(string $filepath): void
    {
        $handle = fopen($filepath, 'r');

        try {
            $this->currentVersion = null;
            $this->currentSection = null;

            while (($buffer = fgets($handle, 4096)) !== false) {
                $this->parseLine($buffer);
            }
        } finally {
            fclose($handle);
        }
    }

    private function parseLine(string $line): void
    {
        if (empty(trim($line))) {
            return;
        }

        if (preg_match(self::PATTERN_VERSION, trim($line), $matches)) {
            $this->currentVersion = $this->addVersion($matches[1], $matches[2] ?? '');
            $this->currentSection = '';
        } elseif (preg_match(self::PATTERN_SECTION, trim($line), $matches)) {
            $this->currentSection = $matches[1];
        } else {
            $this->currentVersion->addChange($line, $this->currentSection);
        }
    }

    public function addVersion(string $name, string $date): ChangelogVersion
    {
        if (!array_key_exists($name, $this->versions)) {
            $version = new ChangelogVersion($name, $date);
            $this->versions[$name] = $version;
        }

        return $this->versions[$name];
    }

    public function sortByDate(): void
    {
        $comparator = function (ChangelogVersion $v1, ChangelogVersion $v2) {
            return $v1->date < $v2->date;
        };

        usort($this->versions, $comparator);
    }

    /**
     * @return ChangelogVersion[]
     */
    public function getVersions(): array
    {
        return $this->versions;
    }
}
