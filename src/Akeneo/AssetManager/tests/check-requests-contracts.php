<?php

declare(strict_types=1);

function writeln($text, $type = 'normal'): void
{
    $RESET = "\e[0m";
    $BOLD_WHITE = "\033[1;38m";
    $GREEN = "\033[0;32m";
    $YELLOW = "\033[0;33m";
    $BG_RED = "\033[41m";

    switch ($type) {
        case 'info':
            echo $GREEN . $text;
            break;
        case 'error':
            echo $BOLD_WHITE . $BG_RED . $text;
            break;
        case 'comment':
            echo $YELLOW . $text;
            break;
        default:
            echo $RESET . $text;
            break;
    }

    echo PHP_EOL;
}

/**
 * @param string[] $result
 *
 * @return string[]
 */
function removeEmptyLine($result): array
{
    if (!empty($result)) {
        unset($result[\count($result) - 1]);
    }

    return $result;
}

function removeDoubleSlashes(array $paths): array
{
    return array_map(
        function (string $path) {
            return str_replace('//', '/', $path);
        },
        $paths
    );
}

/**
 * Find and return the list of request contract
 *
 * @return string[]
 */
function getRequestContracts(string $requestContractsDir): array
{
    $command = sprintf('find %s -type f -name "*.json" -not -path "/*Connector*"', $requestContractsDir);
    $shellResult = shell_exec($command);
    $result = explode("\n", $shellResult);
    $result = removeDoubleSlashes(removeEmptyLine($result));

    return $result;
}

function getRequestContractKey(string $requestContract): string
{
    $requestContractsRoot = sprintf('%s/shared/responses/', __DIR__);

    return str_replace('//', '/', str_replace($requestContractsRoot, '', $requestContract));
}

function isUsedAtPath(string $requestContractKey, string $path): bool
{
    $command = sprintf('grep -rn %s %s', $requestContractKey, $path);
    $shellResult = shell_exec($command);

    return null !== $shellResult;
}

function isUsedInBack(string $requestContract): bool
{
    $testsBackDir = sprintf('%s/back/Integration/UI/Web', __DIR__);
    $requestContractKey = getRequestContractKey($requestContract);

    return isUsedAtPath($requestContractKey, $testsBackDir);
}


function checkUsageInBack(string $requestContract): bool
{
    $hasError = false;
    $isUsedInBack = isUsedInBack($requestContract);
    if (!$isUsedInBack) {
        $message = sprintf('Not used in back: "%s"', getRequestContractKey($requestContract));
        writeln($message);
        $hasError = true;
    }

    return $hasError;
}

function isUsedInFront(string $requestContract): bool
{
    $testsFrontDir = sprintf('%s/front/acceptance', __DIR__);
    $requestContractKey = getRequestContractKey($requestContract);

    return isUsedAtPath($requestContractKey, $testsFrontDir);
}

function checkUsageInFront(string $requestContract): bool
{
    $hasError = false;
    if (!isUsedInFront($requestContract)) {
        $message = sprintf('Not used in front: "%s"', getRequestContractKey($requestContract));
        writeln($message);
        $hasError = true;
    }

    return $hasError;
}

/**
 * @param string[] $requestContracts
 *
 * @return bool
 */
function parseTests($requestContracts): bool
{
    $hasError = false;
    foreach ($requestContracts as $requestContract) {
        $notUsedInBack = checkUsageInBack($requestContract);
        $notUsedInFront = checkUsageInFront($requestContract);

        $hasError = $notUsedInBack || $notUsedInFront;

        if ($notUsedInBack && $notUsedInFront) {
            $message = sprintf('Not used AT ALL: "%s"', getRequestContractKey($requestContract));
            writeln($message, 'error');
        }
    }

    return $hasError;
}

/**
 * Main
 */
$requestContractsDir = sprintf('%s/%s', __DIR__, 'shared/responses/');
$requestContracts = getRequestContracts($requestContractsDir);
$hasError = parseTests($requestContracts);

$exitCode = $hasError ? 1 : 0;
exit($exitCode);
