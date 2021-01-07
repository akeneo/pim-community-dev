#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * This tools generate Rector configuration for moved classes and namespaces.
 *
 * This must be run from the root of a PIM repo, either CE or EE, while
 * providing the Git tag from which we compare to have the list of
 * moved classes and namespaces.
 *
 * For example, to get all classes and namespace moves from 3.2 compared
 * to the current branch:
 * $ std-build/migration/get_renamed_php_classes.php 3.2
 */

use Symfony\Component\Process\Process;

require dirname(__DIR__) . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    die(<<<USAGE
        Missing argument Git ref to diff with!\n

        $ get_renamed_php_classes.php <git_ref> [<additional_rector_config_file1> <additional_rector_config_file2> ...]

USAGE
    );
}

$tag = $argv[1];

$filesToAdd = [];

$renamedClassRectors = [];

if (isset($argv[2])) {
    $filesToAdd = array_slice($argv, 2);

    foreach ($filesToAdd as $fileToAdd) {
        $lines = explode("\n", file_get_contents($fileToAdd));
        foreach ($lines as $line) {
            if (preg_match("/^            ('.+)$/", $line, $matches)) {
                $renamedClassRectors[] = $matches[1];
            }
        }

    }
}

$process = new Process(['git', '-c', 'diff.renameLimit=10000', 'diff', $tag]);

$process->run();

$processOutput = $process->getOutput();

$stream = fopen('php://memory', 'r+');
fwrite($stream, $processOutput);
rewind($stream);

$oldFilePath = null;
$newFilePath = null;

$oldNamespace = null;
$newNamespace = null;

$oldClassname = null;
$newClassname = null;


$inRenameDiff = false;

while ($line = fgets($stream)) {
    if (preg_match('#^rename from (src/.*)$#', $line, $matches)) {
        $oldFilePath = $matches[1];
        $oldClassname = extractClassnameFromFilePath($oldFilePath);

        $inRenameDiff = true;
    }

    if ($inRenameDiff && preg_match('#^rename to (src/.*)$#', $line, $matches)) {
        $newFilePath = $matches[1];
        $newClassname = extractClassnameFromFilePath($newFilePath);
    }

    if (preg_match('#-namespace ([^;]+);$#', $line, $matches)) {
        if ($inRenameDiff) {
            $oldNamespace = $matches[1];
        }
    }

    if (preg_match('#\+namespace ([^;]+);$#', $line, $matches)) {
        if ($inRenameDiff) {
            $newNamespace = $matches[1];
        }
    }

    if (strpos($line, 'diff --git') === 0) {
        if ($inRenameDiff) {
            $inRenameDiff = false;

            if (($oldNamespace !== $newNamespace || $oldClassname !== $newClassname) && $newClassname !== null) {
                if (isRelevantPhpClass($oldFilePath) && isRelevantPhpClass($newFilePath)) {
                    if (null === $oldNamespace && null === $newNamespace) {
                        // No change in namespace, so namespace line in the diff output
                        // So we need to get the namespace from the current file
                        $newNamespace = $oldNamespace = extractNamespaceFromFile($newFilePath);
                    }
                    $renamedClassRectors[$oldNamespace . "\\" . $oldClassname] = $newNamespace . "\\" . $newClassname;
                }
            }
            $oldFilePath = null;
            $newFilePath = null;

            $oldNamespace = null;
            $newNamespace = null;

            $oldClassname = null;
            $newClassname = null;
        }
    }
}

file_put_contents(
    "renamed-classes.php",
    " return ". var_export($renamedClassRectors,true) .";");

function isRelevantPhpClass(string $filePath): bool
{
    if (!preg_match('#\.php$#', $filePath)) {
        return false;
    }

    if ((strpos($filePath, 'spec/') !== false) || (strpos($filePath, 'tests/') !== false)) {
        return false;
    }

    return true;
}

function extractClassnameFromFilePath(string $filePath): string
{
    return str_replace(".php", "", preg_replace('#.*/([^/]+)$#', '$1', $filePath));
}

function extractNamespaceFromFile(string $filePath): string
{
    preg_match("/\nnamespace ([^;]+);/", file_get_contents($filePath), $matches);

    if (!isset($matches[1])) {
        throw new \LogicalException(
            sprintf(
                'Unable to extract the namespace from file %s',
                $filePath
            )
        );
    }

    return $matches[1];
}
