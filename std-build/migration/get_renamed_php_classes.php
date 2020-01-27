#!/usr/bin/env php
<?php

declare(strict_types = 1);

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

require dirname(__DIR__).'/../vendor/autoload.php';

class RenamedClass
{
    private $oldFilePath;
    private $newFilePath;

    private $oldNamespace;
    private $newNamespace;

    private $oldClassname;
    private $newClassname;

    public function __construct(
        string $oldFilePath,
        string $newFilePath,
        ?string $oldNamespace,
        ?string $newNamespace,
        ?string $oldClassname,
        ?string $newClassname
    ) {
        if ($oldNamespace === $newNamespace && $oldClassname === $newClassname) {
            throw new \LogicException(
                sprintf(
                    'File moved from %s to %s, but no changes on namespace and classname detected',
                    $oldFilePath,
                    $newFilePath
                )
            );
        }

        $this->oldFilePath = $oldFilePath;
        $this->newFilePath = $newFilePath;

        $this->oldNamespace = $oldNamespace;
        $this->newNamespace = $newNamespace;

        $this->oldClassname = $oldClassname;
        $this->newClassname = $newClassname;
    }

    private function getOldFqcn(): string
    {
        return $this->oldNamespace . "\\" . $this->oldClassname;
    }

    private function getNewFqcn(): string
    {
        return $this->newNamespace . "\\" . $this->newClassname;
    }

    public function getRector(): string
    {
       return sprintf("'%s': '%s'",
            $this->getOldFqcn(),
            $this->getNewFqcn()
        );
    }
}

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

$stream = fopen('php://memory','r+');
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
                    $renamedClass = new RenamedClass($oldFilePath, $newFilePath, $oldNamespace, $newNamespace, $oldClassname, $newClassname);
                    $renamedClassRectors[] = $renamedClass->getRector();
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

echo <<<YAML
imports:
    - { resource: 'vendor/rector/rector/packages/**/config/config.yaml' }

services:
    Rector\\Renaming\\Rector\\Class_\\RenameClassRector:
        \$oldToNewClasses:

YAML;

$renamedClassRectors = array_unique($renamedClassRectors);

foreach ($renamedClassRectors as $renamedClassRector) {
    echo '            '.$renamedClassRector."\n";
}

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
