<?php

namespace Trial\Storage;

class StoragePathGenerator
{
    /**
     * Result of the method on 1 000 000 iterations
     *      md5($filename)              5.20s user 0.01s system 99% cpu 5.223 total
     *      sha1($filename)             5.23s user 0.01s system 99% cpu 5.244 total
     *      mictotime                   5.36s user 0.00s system 99% cpu 5.368 total
     *      hash('sha1', $filename)     5.41s user 0.02s system 99% cpu 5.429 total
     *      hash('sha256', $filename)   5.80s user 0.01s system 99% cpu 5.829 total
     *      uniqid()                    12.46s user 0.72s system 20% cpu 1:05.74 total
     *      uniqid($filename)           12.42s user 0.70s system 20% cpu 1:05.56 total
     *      md5(microtime() . $filename)    6.18s user 0.01s system 99% cpu 6.205 total    WITHOUT PREG_REPLACE
     *      md5(microtime() . $filename)     7.74s user 0.01s system 99% cpu 7.764 total   WITH_PREG_REPLACE
     *      md5(uniqid() . $filename)       12.96s user 0.74s system 20% cpu 1:06.11 total
     *      md5_file($filename)             10.39s user 2.46s system 99% cpu 12.938 total
     *      sha1_file($filename)            10.31s user 2.64s system 99% cpu 13.050 total
     * File path generation:
     *      - uniqid: bad because too long
     *      - md5/sha1/sha256: bad because all files with the same name will have the same ID
     *      - uniqid/microtime: bad because all files uploaded within the same hour will have the same beginning ID (so same folder)
     *      + combination of md5 + microtime: no duplication on 1 000 000 iterations
     *      + sha1_file/md_file: allow to identify a file, the risk of collision is quasi impossible (no pb with large files)
     * Directory structure:
     *      %s/%s/%s_%s: 2 level of directories which contain 1296 subdirectories maximum each
     *      for 1 000 000 000 files, 595 files per directory if the paths are equally distributed (should be the case
     *      as we md5 the microtime + the filename)
     *
     * @param \SplFileInfo $file
     *
     * @return array
     *               [
     *               'guid' =>  '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12',
     *               'path' => '2f/d4/2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_is_the_file.txt',
     *               'file_name' => '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12_this_is_the_file.txt'
     *               ]
     */
    public function generate(\SplFileInfo $file)
    {
        $guid      = $this->generateId($file->getPathname());
        $sanitized = preg_replace('#[^A-Za-z0-9\.]#', '_', $file->getFilename());
        $path      = sprintf(
            '%s/%s/%s_%s',
            substr($guid, 0, 2),
            substr($guid, 2, 2),
            $guid,
            $sanitized
        );

        return [
            'path'      => $path,
            'guid'      => $guid,
            'file_name' => $sanitized,
        ];
    }

    /**
     * md5      32 characters
     * sha1     40 characters
     * sha256   64 characters
     * uniqid   13 characters
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function generateId($filePath)
    {
        return sha1_file($filePath);
        //return md5_file($filename);
        //return md5(microtime() . $filename);
        //return microtime();
        //return md5(uniqid() . $filename);
        //return md5($filename);
        //return sha1($filename);
        //return hash('sha1', $filename);
        //return hash('sha256', $filename);
        //return uniqid();
        //return uniqid($filename);
    }
}
