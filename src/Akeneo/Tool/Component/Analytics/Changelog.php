<?php

namespace Akeneo\Tool\Component\Analytics;

class Changelog
{
    const PATTERN_VERSION = '/^# (\d.\d.\S+)(?: \((.+)\))?/';
    const PATTERN_SECTION = '/^## (.+)$/';

    /** @var ChangelogVersion[] */
    private $versions = [];
    /** @var ChangelogVersion */
    private $currentVersion;
    /** @var string */
    private $currentSection = '';

    public function parseFile(string $filepath, string $edition): void
    {
        $handle = fopen($filepath, 'r');

        try {
            $this->currentVersion = null;
            $this->currentSection = null;

            while (($buffer = fgets($handle, 4096)) !== false) {
                $this->parseLine($buffer, $edition);
            }
        } finally {
            fclose($handle);
        }
    }

    private function parseLine(string $line, string $edition): void
    {
        if (empty(trim($line))) {
            return;
        }

        if (preg_match(self::PATTERN_VERSION, trim($line), $matches)) {
            $name = sprintf('%s %s', strtoupper($edition), $matches[1]);
            $this->currentVersion = $this->addVersion($name, $matches[2] ?? '');
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

    public function normalize(): array
    {
        $buffer = [];

        foreach ($this->versions as $version) {
            $buffer[] = $version->normalize();
        }

        return $buffer;
    }
}
