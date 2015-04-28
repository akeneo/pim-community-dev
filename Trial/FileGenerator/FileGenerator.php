<?php

namespace Trial\FileGenerator;

class FileGenerator
{
    public function generate($fileName)
    {
        $file = new \SplFileObject($fileName, 'r');
        foreach ($file as $line) {
            if (!empty($line)) {
                list($fileName, $fileSize) = explode(';', $line);
                $fileSize = (int) $fileSize;

                $this->generateOne($fileName, $fileSize);
            }
        }
    }

    /**
     * @param string $fileName
     * @param int    $size     size in byte
     */
    private function generateOne($fileName, $size)
    {
        // in PHP, 1 digit = 1 byte \o/
        $data = str_repeat(0, $size);
        file_put_contents($fileName, $data);
    }
}
