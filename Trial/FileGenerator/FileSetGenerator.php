<?php

namespace Trial\FileGenerator;

class FileSetGenerator
{
    // sizes in bytes
    const KO = 1024;
    const MO = 1048576;
    const GO = 1073741824;

    /** @var int */
    private $nbFiles = 1;

    /** @var \SplFileObject */
    private $log;

    public function generate($nbKilo, $nbMega, $nbGiga)
    {
        $logName = time() . '.fileset';
        $this->log = new \SplFileObject($logName, 'w');

        $this->doGenerate($nbKilo, self::KO);
        $this->doGenerate($nbMega, self::MO);
        $this->doGenerate($nbGiga, self::GO);

        return $logName;
    }

    /**
     * Generates a file set with $nb files of a random size depending of their $sizeUnit
     *
     * @param int $nb
     * @param int $sizeUnit
     */
    private function doGenerate($nb, $sizeUnit)
    {
        for ($i = 0; $i < $nb; $i++) {
            $fileName = sprintf('%s.txt', str_pad($this->nbFiles, 6, '0', STR_PAD_LEFT));

            if (self::KO === $sizeUnit) {
                $size = rand(1, 300) * $sizeUnit;
            } elseif (self::MO === $sizeUnit) {
                $size = rand(1, 10) * $sizeUnit;
            } elseif (self::GO === $sizeUnit) {
                $size = rand(1, 2) * $sizeUnit;
            } else {
                $size = 1;
            }

            $this->log->fwrite(sprintf("%s;%s\n", $fileName, $size));
            $this->nbFiles++;
        }
    }
}
